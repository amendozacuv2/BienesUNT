<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Area;
use App\Models\User;
use App\Services\Users\SearchUsers;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Throwable;

class Index extends Component
{
    use InteractsWithNotifications;
    use WithPagination;
    use WithoutUrlPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $areaId = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAreaId(): void
    {
        $this->resetPage();
    }

    public function destroy(int $userId): void
    {
        abort_unless(Auth::user()?->can('destroy.user'), 403);

        if ((int) Auth::id() === (int) $userId) {
            $this->notifyWarning('No puedes eliminar tu propia cuenta.');

            return;
        }

        $user = User::query()
            ->withCount('auditLogs')
            ->findOrFail($userId);

        if ((int) $user->id === (int) Auth::id()) {
            $this->notifyWarning('No puedes eliminar tu propia cuenta.');

            return;
        }

        if ($user->audit_logs_count > 0) {
            $this->notifyError('No se puede eliminar este usuario porque tiene actividad registrada en el sistema.');

            return;
        }

        try {
            DB::transaction(function () use ($user) {
                $user->areas()->detach();
                $user->syncRoles([]);
                $user->delete();
            });

            $this->notifySuccess('Usuario eliminado correctamente.');
        } catch (Throwable) {
            $this->notifyError('No se pudo eliminar el usuario. Inténtalo nuevamente.');
        }
    }

    public function render(SearchUsers $searchUsers): View
    {
        $query = User::query()
            ->withCount([
                'areas as active_areas_count' => function ($query) {
                    $query->where('user_area.is_active', true);
                },
            ]);

        $searchUsers->apply($query, $this->search, $this->areaId);

        $users = $query
            ->orderBy('name')
            ->orderBy('username')
            ->paginate(10);

        $areas = Area::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.users.index', [
            'users' => $users,
            'areas' => $areas,
        ])->layout('layouts.app', [
            'title' => 'Usuarios',
            'headerTitle' => 'Usuarios',
        ]);
    }
}
