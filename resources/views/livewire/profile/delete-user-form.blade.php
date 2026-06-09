<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section>
    <div class="mb-4">
        <h3 class="card-title font-weight-bold text-danger">Eliminar cuenta</h3>
        <p class="text-muted mb-0">Esta accion elimina permanentemente la cuenta y sus datos relacionados.</p>
    </div>

    <form wire:submit="deleteUser">
        <div class="form-group">
            <x-input-label for="password" :value="'Confirma tu contrasena'" />
            <x-text-input wire:model="password" id="password" name="password" type="password" placeholder="Contrasena actual" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <x-danger-button>
            Eliminar cuenta
        </x-danger-button>
    </form>
</section>
