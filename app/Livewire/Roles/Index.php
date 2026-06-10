<?php

namespace App\Livewire\Roles;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class Index extends Component
{
    use InteractsWithNotifications;
    use WithPagination;
    use WithoutUrlPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function destroy(int $roleId): void
    {
        abort_unless(Auth::user()?->can('destroy.role'), 403);

        $role = Role::query()
            ->where('guard_name', 'web')
            ->withCount('users')
            ->findOrFail($roleId);

        if ($role->users_count > 0) {
            $this->notifyError('No se puede eliminar el rol porque tiene usuarios asignados.');

            return;
        }

        try {
            $role->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->notifySuccess('Rol eliminado correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo eliminar el rol. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->liveSearch($this->search)
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.roles.index', [
            'roles' => $roles,
        ])->layout('layouts.app', [
            'title' => 'Roles',
            'headerTitle' => 'Roles',
        ]);
    }
}
