<?php

namespace App\Livewire\Roles;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class Create extends Component
{
    use InteractsWithNotifications;

    public string $name = '';

    public array $selectedPermissions = [];

    public function save(): void
    {
        abort_unless(Auth::user()?->can('create.role'), 403);

        $validated = $this->validate();

        try {
            $role = Role::create([
                'name' => trim($validated['name']),
                'guard_name' => 'web',
            ]);

            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('id', array_unique($validated['selectedPermissions']))
                ->get();

            $role->syncPermissions($permissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->flashSuccess('Rol creado correctamente.');

            $this->redirectRoute('roles.index');
        } catch (Throwable) {
            $this->notifyError('No se pudo crear el rol. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        return view('livewire.roles.create', [
            'permissions' => $this->permissions(),
        ])->layout('layouts.app', [
            'title' => 'Crear rol',
            'headerTitle' => 'Crear rol',
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
            'selectedPermissions' => [
                'required',
                'array',
                'min:1',
            ],
            'selectedPermissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id')->where('guard_name', 'web'),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.string' => 'El nombre del rol debe ser texto.',
            'name.min' => 'El nombre del rol debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre del rol no debe superar los 255 caracteres.',
            'name.unique' => 'Ya existe un rol registrado con ese nombre.',

            'selectedPermissions.required' => 'Debes seleccionar al menos un permiso.',
            'selectedPermissions.array' => 'Los permisos seleccionados no tienen un formato válido.',
            'selectedPermissions.min' => 'Debes seleccionar al menos un permiso.',

            'selectedPermissions.*.integer' => 'Uno de los permisos seleccionados no es válido.',
            'selectedPermissions.*.distinct' => 'No puedes seleccionar el mismo permiso más de una vez.',
            'selectedPermissions.*.exists' => 'Uno de los permisos seleccionados no existe.',
        ];
    }

    private function permissions(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('order')
            ->orderBy('description')
            ->get(['id', 'name', 'description', 'order']);
    }
}
