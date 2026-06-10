<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\User;
use App\Services\Users\SearchUsers;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Throwable;

class Create extends Component
{
    use InteractsWithNotifications;

    public string $name = '';

    public string $username = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $roleId = '';

    public array $selectedAreas = [];

    public string $isActive = '1';

    public function save(): void
    {
        abort_unless(Auth::user()?->can('create.user'), 403);

        $this->normalizeInput();

        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => app(SearchUsers::class)->cleanForStorage($validated['name']),
                    'username' => app(SearchUsers::class)->cleanUsername($validated['username']),
                    'password' => $validated['password'],
                    'is_active' => $validated['isActive'] === '1',
                ]);

                $role = Role::query()->findOrFail((int) $validated['roleId']);

                $user->syncRoles([$role->name]);

                $this->syncAreas($user, $validated['selectedAreas'] ?? []);
            });

            $this->flashSuccess('Usuario creado correctamente.');

            $this->redirectRoute('users.index');
        } catch (Throwable) {
            $this->notifyError('No se pudo crear el usuario. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        return view('livewire.users.create', [
            'roles' => $this->roles(),
            'areas' => $this->areas(),
        ])->layout('layouts.app', [
            'title' => 'Crear usuario',
            'headerTitle' => 'Crear usuario',
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
            'username' => [
                'required',
                'string',
                'min:4',
                'max:100',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'same:passwordConfirmation',
            ],
            'passwordConfirmation' => [
                'required',
                'string',
                'min:8',
            ],
            'roleId' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
            'selectedAreas' => [
                'array',
            ],
            'selectedAreas.*' => [
                'integer',
                'exists:areas,id',
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
            'name.required' => 'El nombre del usuario es obligatorio.',
            'name.string' => 'El nombre del usuario debe ser texto.',
            'name.min' => 'El nombre del usuario debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre del usuario no debe superar los 150 caracteres.',

            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.string' => 'El nombre de usuario debe ser texto.',
            'username.min' => 'El nombre de usuario debe tener al menos 4 caracteres.',
            'username.max' => 'El nombre de usuario no debe superar los 100 caracteres.',
            'username.regex' => 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'username.unique' => 'Ya existe un usuario registrado con ese nombre de usuario.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.same' => 'La confirmación de contraseña no coincide.',

            'passwordConfirmation.required' => 'La confirmación de contraseña es obligatoria.',
            'passwordConfirmation.string' => 'La confirmación de contraseña debe ser texto.',
            'passwordConfirmation.min' => 'La confirmación de contraseña debe tener al menos 8 caracteres.',

            'roleId.required' => 'El rol del usuario es obligatorio.',
            'roleId.integer' => 'El rol seleccionado no es válido.',
            'roleId.exists' => 'El rol seleccionado no existe.',

            'selectedAreas.array' => 'Las áreas seleccionadas no son válidas.',
            'selectedAreas.*.integer' => 'Una de las áreas seleccionadas no es válida.',
            'selectedAreas.*.exists' => 'Una de las áreas seleccionadas no existe.',

            'isActive.required' => 'El estado del usuario es obligatorio.',
            'isActive.in' => 'El estado seleccionado no es válido.',
        ];
    }

    private function normalizeInput(): void
    {
        $this->name = app(SearchUsers::class)->cleanForStorage($this->name);
        $this->username = app(SearchUsers::class)->cleanUsername($this->username);
        $this->selectedAreas = array_values(array_unique(array_map('intval', $this->selectedAreas)));
    }

    private function syncAreas(User $user, array $areas): void
    {
        if (empty($areas)) {
            $user->areas()->sync([]);

            return;
        }

        $payload = collect($areas)
            ->mapWithKeys(function (int $areaId) {
                return [
                    $areaId => [
                        'is_active' => true,
                    ],
                ];
            })
            ->toArray();

        $user->areas()->sync($payload);
    }

    private function roles()
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function areas()
    {
        return Area::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
