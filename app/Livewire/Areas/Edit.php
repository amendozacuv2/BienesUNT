<?php

namespace App\Livewire\Areas;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Services\Areas\SearchAreas;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Throwable;

class Edit extends Component
{
    use InteractsWithNotifications;

    public Area $area;

    public string $name = '';

    public string $isActive = '1';

    public function mount(Area $area): void
    {
        abort_unless(Auth::user()?->can('edit.area'), 403);

        $this->area = $area;
        $this->name = $area->name;
        $this->isActive = $area->is_active ? '1' : '0';
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->can('edit.area'), 403);

        $this->validateAreaName();

        $validated = $this->validate();

        try {
            $this->area->update([
                'name' => app(SearchAreas::class)->cleanForStorage($validated['name']),
                'is_active' => $validated['isActive'] === '1',
            ]);

            $this->notifySuccess('Área actualizada correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo actualizar el área. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        return view('livewire.areas.edit')
            ->layout('layouts.app', [
                'title' => 'Editar área',
                'headerTitle' => 'Editar área',
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
            'isActive' => [
                'required',
                'in:0,1',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del área es obligatorio.',
            'name.string' => 'El nombre del área debe ser texto.',
            'name.min' => 'El nombre del área debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre del área no debe superar los 150 caracteres.',

            'isActive.required' => 'El estado del área es obligatorio.',
            'isActive.in' => 'El estado seleccionado no es válido.',
        ];
    }

    private function validateAreaName(): void
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if ($validator->errors()->has('name')) {
                    return;
                }

                if (app(SearchAreas::class)->existsNormalizedName($this->name, $this->area->id)) {
                    $validator->errors()->add(
                        'name',
                        'Ya existe un área registrada con ese nombre.'
                    );
                }
            });
        });
    }
}
