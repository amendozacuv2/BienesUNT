<?php

namespace App\Livewire\Estates;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Livewire\Estates\Concerns\InteractsWithEstateFieldSuggestions;
use App\Models\Estate;
use App\Services\Estates\EstateFormRowsService;
use App\Services\Estates\EstateLocationAccessService;
use App\Services\Estates\NormalizeEstateData;
use App\Services\Estates\SaveEstateBatch;
use App\Services\Estates\SearchEstates;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class Create extends Component
{
    use InteractsWithNotifications;
    use WithPagination;
    use InteractsWithEstateFieldSuggestions;

    protected string $paginationTheme = 'bootstrap';

    public string $areaId = '';

    public string $locationId = '';

    public string $existingSearch = '';

    public string $existingPerPage = '10';

    public array $rows = [];

    public array $openedRows = [];

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('create.estate'), 403);

        $row = app(EstateFormRowsService::class)->emptyRow();

        $this->rows = [$row];
        $this->openedRows = [
            $row['row_key'] => true,
        ];
    }

    public function updatedAreaId(): void
    {
        $this->locationId = '';
    }

    public function updatedExistingSearch(): void
    {
        $this->resetPage('existingEstatesPage');
    }

    public function updatedExistingPerPage(): void
    {
        if (! in_array($this->existingPerPage, ['10', '25', '50'], true)) {
            $this->existingPerPage = '10';
        }

        $this->resetPage('existingEstatesPage');
    }

    public function addBlankRow(): void
    {
        $row = app(EstateFormRowsService::class)->emptyRow();

        $this->rows[] = $row;
        $this->openedRows[$row['row_key']] = true;
    }

    public function removeRow(int $index): void
    {
        $rowKey = $this->rows[$index]['row_key'] ?? null;

        $this->rows = app(EstateFormRowsService::class)->removeAt($this->rows, $index);

        if ($rowKey) {
            unset($this->openedRows[$rowKey]);
        }

        $this->syncOpenedRows();
    }

    public function toggleRow(string $rowKey): void
    {
        $this->openedRows[$rowKey] = ! ($this->openedRows[$rowKey] ?? false);
    }

    public function addExistingEstate(string $uuid): void
    {
        abort_unless(Auth::user()?->can('edit.estate'), 403);

        $rowsService = app(EstateFormRowsService::class);

        if ($rowsService->containsEstate($this->rows, $uuid)) {
            $this->notifyInfo('Este bien ya está agregado en el formulario.');
            return;
        }

        $estate = Estate::query()
            ->with(['location.area'])
            ->where('uuid', $uuid)
            ->first();

        if (! $estate) {
            $this->notifyError('No se encontró el bien seleccionado.');
            return;
        }

        $this->rows = $rowsService->addEstateToRows($this->rows, $estate);

        foreach ($this->rows as $row) {
            if (($row['existing_uuid'] ?? '') === $uuid) {
                $this->openedRows[$row['row_key']] = true;
                break;
            }
        }

        $this->syncOpenedRows();

        $this->existingSearch = '';
        $this->resetPage('existingEstatesPage');

        $this->notifyInfo('El bien fue agregado para actualizar sus datos.');
    }

    public function save(): void
    {
        $validated = $this->validateRows();

        try {
            $result = app(SaveEstateBatch::class)->handle(
                Auth::user(),
                (int) $validated['areaId'],
                (int) $validated['locationId'],
                $validated['rows']
            );

            $row = app(EstateFormRowsService::class)->emptyRow();

            $this->rows = [$row];
            $this->openedRows = [
                $row['row_key'] => true,
            ];

            $this->existingSearch = '';
            $this->resetPage('existingEstatesPage');

            $message = trim(
                ($result['created'] > 0 ? $result['created'] . ' bien(es) creado(s). ' : '') .
                ($result['updated'] > 0 ? $result['updated'] . ' bien(es) actualizado(s).' : '')
            );

            $this->notifySuccess($message !== '' ? $message : 'Los bienes fueron procesados correctamente.');
        } catch (AuthorizationException) {
            abort(403);
        } catch (InvalidArgumentException $exception) {
            $this->notifyError($exception->getMessage());
        } catch (Throwable) {
            $this->notifyError('No se pudieron guardar los bienes. Revisa los datos e intenta nuevamente.');
        }
    }

    public function render(): View
    {
        abort_unless(Auth::user()?->can('create.estate'), 403);

        $user = Auth::user();
        $areaId = $this->areaId !== '' ? (int) $this->areaId : null;
        $locationAccess = app(EstateLocationAccessService::class);

        $existingEstates = $user?->can('edit.estate')
            ? app(SearchEstates::class)->forCreateSearch(
                $this->existingSearch,
                $this->normalizedExistingPerPage(),
                'existingEstatesPage'
            )
            : collect();

        return view('livewire.estates.create', [
            'areas' => $locationAccess->activeAreasForUser($user),
            'locations' => $locationAccess->activeLocationsForUserArea($user, $areaId),
            'selectedArea' => $locationAccess->selectedAreaForView($user, $areaId),
            'existingEstates' => $existingEstates,
            'situations' => Estate::SITUATIONS,
            'conservationStatuses' => Estate::CONSERVATION_STATUSES,
        ])->layout('layouts.app', [
            'title' => 'Crear bienes',
            'headerTitle' => 'Crear bienes',
        ]);
    }

    private function validateRows(): array
    {
        $input = [
            'areaId' => $this->areaId,
            'locationId' => $this->locationId,
            'rows' => array_values($this->rows),
        ];

        $validator = Validator::make($input, [
            'areaId' => ['required', 'integer'],
            'locationId' => ['required', 'integer'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.existing_uuid' => ['nullable', 'string', 'exists:estates,uuid'],
            'rows.*.patrimonial_code' => ['nullable', 'string', 'max:100'],
            'rows.*.internal_code' => ['required', 'string', 'max:100'],
            'rows.*.denomination' => ['nullable', 'string', 'max:255'],
            'rows.*.brand' => ['nullable', 'string', 'max:150'],
            'rows.*.model' => ['nullable', 'string', 'max:150'],
            'rows.*.type' => ['nullable', 'string', 'max:150'],
            'rows.*.color' => ['nullable', 'string', 'max:100'],
            'rows.*.series' => ['nullable', 'string', 'max:150'],
            'rows.*.dimensions' => ['nullable', 'string'],
            'rows.*.others' => ['nullable', 'string'],
            'rows.*.situation' => ['required', 'string', Rule::in(Estate::SITUATIONS)],
            'rows.*.conservation_status' => ['required', 'string', Rule::in(Estate::CONSERVATION_STATUSES)],
            'rows.*.observation' => ['nullable', 'string'],
        ], [
            'areaId.required' => 'Selecciona el área.',
            'locationId.required' => 'Selecciona la ubicación.',
            'rows.required' => 'Agrega al menos un bien.',
            'rows.min' => 'Agrega al menos un bien.',
            'rows.*.internal_code.required' => 'El código interno es obligatorio.',
            'rows.*.internal_code.max' => 'El código interno no debe superar 100 caracteres.',
            'rows.*.situation.required' => 'Selecciona la situación.',
            'rows.*.conservation_status.required' => 'Selecciona el estado de conservación.',
        ], [
            'areaId' => 'área',
            'locationId' => 'ubicación',
            'rows.*.patrimonial_code' => 'código patrimonial',
            'rows.*.internal_code' => 'código interno',
            'rows.*.denomination' => 'denominación',
            'rows.*.brand' => 'marca',
            'rows.*.model' => 'modelo',
            'rows.*.type' => 'tipo',
            'rows.*.color' => 'color',
            'rows.*.series' => 'serie',
            'rows.*.dimensions' => 'dimensiones',
            'rows.*.others' => 'otros',
            'rows.*.situation' => 'situación',
            'rows.*.conservation_status' => 'estado de conservación',
            'rows.*.observation' => 'observación',
        ]);

        $validator->after(function ($validator) use ($input) {
            $user = Auth::user();
            $normalizer = app(NormalizeEstateData::class);
            $locationAccess = app(EstateLocationAccessService::class);

            $areaId = (int) $this->areaId;
            $locationId = (int) $this->locationId;

            if (! $locationAccess->canAccessArea($user, $areaId)) {
                $validator->errors()->add('areaId', 'Selecciona un área asignada a tu usuario.');
            }

            if (! $locationAccess->findAllowedLocation($user, $areaId, $locationId)) {
                $validator->errors()->add('locationId', 'Selecciona una ubicación activa del área elegida.');
            }

            $codes = [];

            foreach (array_values($input['rows']) as $index => $row) {
                $key = $normalizer->internalCodeKey($row['internal_code'] ?? null);

                if ($key === '') {
                    continue;
                }

                if (isset($codes[$key])) {
                    $validator->errors()->add("rows.$index.internal_code", 'El código interno está repetido en el formulario.');
                    continue;
                }

                $codes[$key] = true;

                $query = Estate::query()
                    ->whereRaw('LOWER(internal_code) = ?', [$key]);

                if (! empty($row['existing_uuid'])) {
                    $query->where('uuid', '<>', $row['existing_uuid']);
                }

                if ($query->exists()) {
                    $validator->errors()->add("rows.$index.internal_code", 'Este código interno ya se encuentra registrado.');
                }
            }
        });

        return $validator->validate();
    }

    private function syncOpenedRows(): void
    {
        $currentKeys = collect($this->rows)
            ->pluck('row_key')
            ->filter()
            ->values()
            ->all();

        $this->openedRows = collect($this->openedRows)
            ->only($currentKeys)
            ->all();

        if ($currentKeys === []) {
            return;
        }

        $hasOpenedRow = collect($currentKeys)
            ->contains(fn ($key) => (bool) ($this->openedRows[$key] ?? false));

        if (! $hasOpenedRow) {
            $this->openedRows[$currentKeys[0]] = true;
        }
    }

    private function normalizedExistingPerPage(): int
    {
        return in_array($this->existingPerPage, ['10', '25', '50'], true)
            ? (int) $this->existingPerPage
            : 10;
    }
}