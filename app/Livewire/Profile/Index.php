<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';

    public string $username = '';

    public string $status = '';

    public string $areas = '';

    public string $roles = '';

    public function mount(): void
    {
        $user = $this->authenticatedUser();

        abort_unless($user->can('view.profile'), 403);

        $user->loadMissing([
            'activeAreas' => fn ($query) => $query->orderBy('name'),
            'roles' => fn ($query) => $query->orderBy('name'),
        ]);

        $this->name = $user->name;
        $this->username = $user->username;
        $this->status = $user->is_active ? 'ACTIVO' : 'INACTIVO';

        $this->areas = $user->activeAreas
            ->pluck('name')
            ->filter()
            ->values()
            ->implode(', ') ?: 'Sin áreas asignadas';

        $this->roles = $user->roles
            ->pluck('name')
            ->filter()
            ->values()
            ->implode(', ') ?: 'Sin rol asignado';
    }

    public function render(): View
    {
        return view('livewire.profile.index')
            ->layout('layouts.app', [
                'title' => 'Perfil',
                'headerTitle' => 'Perfil',
            ]);
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
