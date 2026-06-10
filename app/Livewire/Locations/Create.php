<?php

namespace App\Livewire\Locations;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\Location;
use App\Services\Locations\SearchLocations;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Throwable;

class Create extends Component
{
    use InteractsWithNotifications;

    public string $name = '';

    public string $areaId = '';

    public string $isActive = '1';

    public function save(): void
    {
        abort_unless(Auth::user()?->can('create.location'), 403);

        $this->validateLocationName();

        $validated = $this->validate();

        try {
            Location::create([
                'name' => app(SearchLocations::class)->cleanForStorage($validated['name']),
                'area_id' => (int) $validated['areaId'],
                'is_active' => $validated['isActive'] === '1',
            ]);

            $this->flashSuccess('Ubicación creada correctamente.');

            $this->redirectRoute('locations.index');
        } catch (Throwable) {
            $this->notifyError('No se pudo crear la ubicación. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        $areas = $this->allowedAreas();

        return view('livewire.locations.create', [
            'areas' => $areas,
        ])->layout('layouts.app', [
            'title' => 'Crear ubicación',
            'headerTitle' => 'Crear ubicación',
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],
            'areaId' => [
                'required',
                'integer',
                Rule::in($this->allowedAreaIds()),
            ],
            'isActive' => [
                'required',
                'in:0,1',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre de la ubicación es obligatorio.',
            'name.string' => 'El nombre de la ubicación debe ser texto.',
            'name.min' => 'El nombre de la ubicación debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre de la ubicación no debe superar los 150 caracteres.',

            'areaId.required' => 'El área de la ubicación es obligatoria.',
            'areaId.integer' => 'El área seleccionada no es válida.',
            'areaId.in' => 'Solo puedes seleccionar áreas que tienes asignadas.',

            'isActive.required' => 'El estado de la ubicación es obligatorio.',
            'isActive.in' => 'El estado seleccionado no es válido.',
        ];
    }

    private function validateLocationName(): void
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->has('name') || $validator->errors()->has('areaId')) {
                    return;
                }

                if (app(SearchLocations::class)->existsNormalizedName($this->name, (int) $this->areaId)) {
                    $validator->errors()->add(
                        'name',
                        'Ya existe una ubicación registrada con ese nombre en el área seleccionada.'
                    );
                }
            });
        });
    }

    private function allowedAreas()
    {
        return Area::query()
            ->whereIn('id', $this->allowedAreaIds())
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function allowedAreaIds(): array
    {
        return Auth::user()
            ?->activeAreas()
            ->pluck('areas.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray() ?? [];
    }

}
