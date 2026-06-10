<?php

namespace App\Livewire\Estates;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Livewire\Estates\Concerns\InteractsWithEstateFieldSuggestions;
use App\Models\Area;
use App\Models\Estate;
use App\Models\Location;
use App\Services\Estates\EstateAuditLogger;
use App\Services\Estates\EstateFieldSuggestions;
use App\Services\Estates\NormalizeEstateData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class Edit extends Component
{
    use InteractsWithNotifications;
    use InteractsWithEstateFieldSuggestions;

    public Estate $estate;

    public string $areaId = '';

    public string $locationId = '';

    public string $patrimonialCode = '';

    public string $internalCode = '';

    public string $denomination = '';

    public string $brand = '';

    public string $model = '';

    public string $type = '';

    public string $color = '';

    public string $series = '';

    public string $dimensions = '';

    public string $others = '';

    public string $situation = '';

    public string $conservationStatus = '';

    public string $observation = '';

    public function mount(Estate $estate): void
    {
        abort_unless(Auth::user()?->can('edit.estate'), 403);

        $estate->load(['location.area']);

        abort_unless(
            $estate->location && in_array((int) $estate->location->area_id, $this->assignedAreaIds(), true),
            403
        );

        $this->estate = $estate;

        $this->areaId = (string) $estate->location->area_id;
        $this->locationId = (string) $estate->location_id;
        $this->patrimonialCode = $estate->patrimonial_code ?? '';
        $this->internalCode = $estate->internal_code ?? '';
        $this->denomination = $estate->denomination ?? '';
        $this->brand = $estate->brand ?? '';
        $this->model = $estate->model ?? '';
        $this->type = $estate->type ?? '';
        $this->color = $estate->color ?? '';
        $this->series = $estate->series ?? '';
        $this->dimensions = $estate->dimensions ?? '';
        $this->others = $estate->others ?? '';
        $this->situation = $estate->situation ?: Estate::SITUATIONS[0];
        $this->conservationStatus = $estate->conservation_status ?: Estate::CONSERVATION_STATUSES[0];
        $this->observation = $estate->observation ?? '';
    }

    public function updatedAreaId(): void
    {
        $this->locationId = '';
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->can('edit.estate'), 403);

        $validated = $this->validateEstate();

        $location = $this->findSelectedLocation(
            (int) $validated['locationId'],
            (int) $validated['areaId']
        );

        if (! $location) {
            $this->notifyError('Selecciona una ubicación válida para actualizar el bien.');
            return;
        }

        try {
            DB::transaction(function () use ($validated, $location) {
                $logger = app(EstateAuditLogger::class);
                $normalizer = app(NormalizeEstateData::class);

                $this->estate->load(['location.area']);

                $oldValues = $logger->estateValues($this->estate);
                $oldLocationId = (int) $this->estate->location_id;

                $this->estate->update([
                    'location_id' => (int) $location->id,
                    'patrimonial_code' => $normalizer->cleanText($validated['patrimonialCode'] ?? null),
                    'internal_code' => $normalizer->cleanInternalCode($validated['internalCode'] ?? null),
                    'denomination' => $normalizer->cleanText($validated['denomination'] ?? null),
                    'brand' => $normalizer->cleanText($validated['brand'] ?? null),
                    'model' => $normalizer->cleanText($validated['model'] ?? null),
                    'type' => $normalizer->cleanText($validated['type'] ?? null),
                    'color' => $normalizer->cleanText($validated['color'] ?? null),
                    'series' => $normalizer->cleanText($validated['series'] ?? null),
                    'dimensions' => $normalizer->cleanText($validated['dimensions'] ?? null),
                    'others' => $normalizer->cleanText($validated['others'] ?? null),
                    'situation' => $normalizer->normalizeSituation($validated['situation'] ?? null),
                    'conservation_status' => $normalizer->normalizeConservationStatus($validated['conservationStatus'] ?? null),
                    'observation' => $normalizer->cleanText($validated['observation'] ?? null),
                ]);

                $this->estate->refresh()->load(['location.area']);

                $newValues = $logger->estateValues($this->estate);

                $logger->updated($this->estate, $oldValues, $newValues);

                if ($oldLocationId !== (int) $this->estate->location_id) {
                    $logger->changedLocation($this->estate, $oldValues, $newValues);
                }
            });

            app(EstateFieldSuggestions::class)->invalidateCache();

            $this->flashSuccess('El bien fue actualizado correctamente.');

            $this->redirectRoute('estates.index', navigate: true);
        } catch (Throwable) {
            $this->notifyError('No se pudo actualizar el bien. Revisa los datos e intenta nuevamente.');
        }
    }

    public function render(): View
    {
        abort_unless(Auth::user()?->can('edit.estate'), 403);

        $allowedAreaIds = $this->assignedAreaIds();

        $areas = Area::query()
            ->whereIn('id', $allowedAreaIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->whereIn('area_id', $allowedAreaIds)
            ->when($this->areaId !== '', function ($query) {
                $query->where('area_id', (int) $this->areaId);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedArea = $this->selectedAreaForView($allowedAreaIds);

        return view('livewire.estates.edit', [
            'areas' => $areas,
            'locations' => $locations,
            'selectedArea' => $selectedArea,
            'situations' => Estate::SITUATIONS,
            'conservationStatuses' => Estate::CONSERVATION_STATUSES,
        ])->layout('layouts.app', [
            'title' => 'Editar bien',
            'headerTitle' => 'Editar bien',
        ]);
    }

    private function validateEstate(): array
    {
        $input = [
            'areaId' => $this->areaId,
            'locationId' => $this->locationId,
            'patrimonialCode' => $this->patrimonialCode,
            'internalCode' => $this->internalCode,
            'denomination' => $this->denomination,
            'brand' => $this->brand,
            'model' => $this->model,
            'type' => $this->type,
            'color' => $this->color,
            'series' => $this->series,
            'dimensions' => $this->dimensions,
            'others' => $this->others,
            'situation' => $this->situation,
            'conservationStatus' => $this->conservationStatus,
            'observation' => $this->observation,
        ];

        $validator = Validator::make($input, [
            'areaId' => ['required', 'integer'],
            'locationId' => ['required', 'integer'],
            'patrimonialCode' => ['nullable', 'string', 'max:100'],
            'internalCode' => ['required', 'string', 'max:100'],
            'denomination' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'string', 'max:150'],
            'color' => ['nullable', 'string', 'max:100'],
            'series' => ['nullable', 'string', 'max:150'],
            'dimensions' => ['nullable', 'string'],
            'others' => ['nullable', 'string'],
            'situation' => ['required', 'string', Rule::in(Estate::SITUATIONS)],
            'conservationStatus' => ['required', 'string', Rule::in(Estate::CONSERVATION_STATUSES)],
            'observation' => ['nullable', 'string'],
        ], [
            'areaId.required' => 'Selecciona el área.',
            'locationId.required' => 'Selecciona la ubicación.',
            'internalCode.required' => 'El código interno es obligatorio.',
            'situation.required' => 'Selecciona la situación.',
            'conservationStatus.required' => 'Selecciona el estado de conservación.',
        ], [
            'areaId' => 'área',
            'locationId' => 'ubicación',
            'patrimonialCode' => 'código patrimonial',
            'internalCode' => 'código interno',
            'denomination' => 'denominación',
            'brand' => 'marca',
            'model' => 'modelo',
            'type' => 'tipo',
            'color' => 'color',
            'series' => 'serie',
            'dimensions' => 'dimensiones',
            'others' => 'otros',
            'situation' => 'situación',
            'conservationStatus' => 'estado de conservación',
            'observation' => 'observación',
        ]);

        $validator->after(function ($validator) {
            $normalizer = app(NormalizeEstateData::class);
            $internalCode = $normalizer->cleanInternalCode($this->internalCode);

            if (! in_array((int) $this->areaId, $this->assignedAreaIds(), true)) {
                $validator->errors()->add('areaId', 'Selecciona un área asignada a tu usuario.');
            }

            if (! $this->findSelectedLocation((int) $this->locationId, (int) $this->areaId)) {
                $validator->errors()->add('locationId', 'Selecciona una ubicación activa del área elegida.');
            }

            if ($internalCode !== '') {
                $exists = Estate::query()
                    ->whereRaw('LOWER(internal_code) = ?', [mb_strtolower($internalCode)])
                    ->where('id', '<>', $this->estate->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('internalCode', 'Este código interno ya se encuentra registrado.');
                }
            }
        });

        return $validator->validate();
    }

    private function findSelectedLocation(int $locationId, int $areaId): ?Location
    {
        if ($locationId <= 0 || $areaId <= 0) {
            return null;
        }

        return Location::query()
            ->whereKey($locationId)
            ->where('area_id', $areaId)
            ->whereIn('area_id', $this->assignedAreaIds())
            ->where('is_active', true)
            ->first();
    }

    private function selectedAreaForView(array $allowedAreaIds): ?Area
    {
        if ($this->areaId === '') {
            return null;
        }

        return Area::query()
            ->with(['employees' => function ($query) {
                $query
                    ->where('employees.is_active', true)
                    ->where('employee_area.is_active', true)
                    ->orderBy('lastname')
                    ->orderBy('name');
            }])
            ->whereIn('id', $allowedAreaIds)
            ->where('is_active', true)
            ->find((int) $this->areaId);
    }

    private function assignedAreaIds(): array
    {
        return Auth::user()
            ?->activeAreas()
            ->pluck('areas.id')
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];
    }
}
