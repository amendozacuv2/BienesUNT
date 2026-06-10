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

class Edit extends Component
{
    use InteractsWithNotifications;

    public User $user;

    public string $name = '';

    public string $username = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $roleId = '';

    public array $selectedAreas = [];

    public string $isActive = '1';

    public function mount(User $user): void
    {
        abort_unless(Auth::user()?->can('edit.user'), 403);

        $user->load(['roles', 'areas']);

        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->roleId = optional($user->roles->first())->id
            ? (string) $user->roles->first()->id
            : '';
        $this->selectedAreas = $user->areas
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
        $this->isActive = $user->is_active ? '1' : '0';
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->can('edit.user'), 403);

        $this->normalizeInput();

        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                $data = [
                    'name' => app(SearchUsers::class)->cleanForStorage($validated['name']),
                    'username' => app(SearchUsers::class)->cleanUsername($validated['username']),
                    'is_active' => $validated['isActive'] === '1',
                ];

                if (filled($validated['password'] ?? null)) {
                    $data['password'] = $validated['password'];
                }

                $this->user->update($data);

                $role = Role::query()->findOrFail((int) $validated['roleId']);

                $this->user->syncRoles([$role->name]);

                $this->syncAreas($this->user, $validated['selectedAreas'] ?? []);
            });

            $this->password = '';
            $this->passwordConfirmation = '';
            $this->user->refresh();

            $this->notifySuccess('Usuario actualizado correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo actualizar el usuario. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        return view('livewire.users.edit', [
            'roles' => $this->roles(),
            'areas' => $this->areas(),
        ])->layout('layouts.app', [
            'title' => 'Editar usuario',
            'headerTitle' => 'Editar usuario',
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
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'same:passwordConfirmation',
            ],
            'passwordConfirmation' => [
                'nullable',
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
            'username.unique' => 'Ya existe otro usuario registrado con ese nombre de usuario.',

            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.same' => 'La confirmación de contraseña no coincide.',

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
        $selectedAreaIds = collect($this->selectedAreas)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        return Area::query()
            ->where(function ($query) use ($selectedAreaIds) {
                $query->where('is_active', true);

                if ($selectedAreaIds->isNotEmpty()) {
                    $query->orWhereIn('id', $selectedAreaIds);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
