<?php

namespace App\Livewire\Employees;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\Employee;
use App\Services\Employees\NormalizeApi;
use App\Services\Employees\SeekerApi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class Edit extends Component
{
    use InteractsWithNotifications;

    public Employee $employee;

    public string $dni = '';

    public string $originalDni = '';

    public string $name = '';

    public string $lastname = '';

    public ?string $areaId = null;

    public string $isActive = '1';

    public bool $identityFieldsEnabled = true;

    public function mount(Employee $employee): void
    {
        abort_unless(Auth::user()?->can('edit.employee'), 403);

        $allowedAreaIds = $this->allowedAreaIds();

        abort_unless(
            $employee->areas()
                ->whereIn('areas.id', $allowedAreaIds)
                ->where('employee_area.is_active', true)
                ->exists(),
            403
        );

        $employee->load('areas');

        $this->employee = $employee;
        $this->dni = $employee->dni;
        $this->originalDni = $employee->dni;
        $this->name = $employee->name;
        $this->lastname = $employee->lastname;

        $this->areaId = optional(
            $employee->areas
                ->whereIn('id', $allowedAreaIds)
                ->first()
        )->id
            ? (string) $employee->areas->whereIn('id', $allowedAreaIds)->first()->id
            : null;

        $this->isActive = $employee->is_active ? '1' : '0';
        $this->identityFieldsEnabled = mb_strlen($this->dni) === 8;
    }

    public function updatedDni(string $value): void
    {
        $this->dni = app(NormalizeApi::class)->cleanDni($value);
        $this->identityFieldsEnabled = mb_strlen($this->dni) === 8;

        $this->resetValidation(['dni', 'name', 'lastname']);

        if (! $this->identityFieldsEnabled) {
            $this->name = '';
            $this->lastname = '';

            return;
        }

        if ($this->dni === $this->originalDni) {
            $this->name = $this->employee->name;
            $this->lastname = $this->employee->lastname;

            return;
        }

        if (
            Employee::query()
                ->where('dni', $this->dni)
                ->whereKeyNot($this->employee->id)
                ->exists()
        ) {
            $this->notifyWarning('Ya existe otro empleado registrado con este DNI.');

            return;
        }

        $this->fillIdentityFromApi();
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->can('edit.employee'), 403);

        $this->normalizeNullableArea();

        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                $this->employee->update([
                    'dni' => app(NormalizeApi::class)->cleanDni($validated['dni']),
                    'name' => app(NormalizeApi::class)->cleanName($validated['name']),
                    'lastname' => app(NormalizeApi::class)->cleanName($validated['lastname']),
                    'is_active' => $validated['isActive'] === '1',
                ]);

                $this->syncArea($this->employee, $validated['areaId'] ?? null);
            });

            $this->originalDni = $this->dni;
            $this->employee->refresh();

            $this->notifySuccess('Empleado actualizado correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo actualizar el empleado. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        $areas = $this->allowedAreas();

        if (filled($this->areaId) && ! in_array((int) $this->areaId, $this->allowedAreaIds(), true)) {
            $this->areaId = null;
        }

        return view('livewire.employees.edit', [
            'areas' => $areas,
        ])->layout('layouts.app', [
            'title' => 'Editar empleado',
            'headerTitle' => 'Editar empleado',
        ]);
    }

    protected function rules(): array
    {
        return [
            'dni' => [
                'required',
                'digits:8',
                Rule::unique('employees', 'dni')->ignore($this->employee->id),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],
            'lastname' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],
            'areaId' => [
                'nullable',
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
            'dni.required' => 'El DNI del empleado es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Ya existe otro empleado registrado con este DNI.',

            'name.required' => 'El nombre del empleado es obligatorio.',
            'name.string' => 'El nombre del empleado debe ser texto.',
            'name.min' => 'El nombre del empleado debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre del empleado no debe superar los 120 caracteres.',

            'lastname.required' => 'El apellido del empleado es obligatorio.',
            'lastname.string' => 'El apellido del empleado debe ser texto.',
            'lastname.min' => 'El apellido del empleado debe tener al menos 2 caracteres.',
            'lastname.max' => 'El apellido del empleado no debe superar los 120 caracteres.',

            'areaId.integer' => 'El área seleccionada no es válida.',
            'areaId.in' => 'Solo puedes asignar áreas que tienes disponibles.',

            'isActive.required' => 'El estado del empleado es obligatorio.',
            'isActive.in' => 'El estado seleccionado no es válido.',
        ];
    }

    private function fillIdentityFromApi(): void
    {
        $response = app(SeekerApi::class)->search($this->dni);

        if (! $response['found']) {
            $this->name = '';
            $this->lastname = '';
            $this->notifyWarning($response['message']);

            return;
        }

        $data = app(NormalizeApi::class)->employeeData($response['data']);

        $this->name = $data['name'];
        $this->lastname = $data['lastname'];

        $this->notifySuccess('DNI actualizado. Completamos los datos automáticamente.');
    }

    private function normalizeNullableArea(): void
    {
        if ($this->areaId === '') {
            $this->areaId = null;
        }
    }

    private function syncArea(Employee $employee, mixed $areaId): void
    {
        if (blank($areaId)) {
            $employee->areas()->sync([]);

            return;
        }

        $areaId = (int) $areaId;

        if (! in_array($areaId, $this->allowedAreaIds(), true)) {
            return;
        }

        $employee->areas()->sync([
            $areaId => [
                'is_active' => true,
            ],
        ]);
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
