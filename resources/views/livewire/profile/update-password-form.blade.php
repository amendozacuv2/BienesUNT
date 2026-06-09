<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <div class="mb-4">
        <h3 class="card-title font-weight-bold">Actualizar contrasena</h3>
        <p class="text-muted mb-0">Usa una contrasena segura para proteger tu cuenta.</p>
    </div>

    <form wire:submit="updatePassword">
        <div class="form-group">
            <x-input-label for="update_password_current_password" :value="'Contrasena actual'" />
            <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
        </div>

        <div class="form-group">
            <x-input-label for="update_password_password" :value="'Nueva contrasena'" />
            <x-text-input wire:model="password" id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="form-group">
            <x-input-label for="update_password_password_confirmation" :value="'Confirmar contrasena'" />
            <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="d-flex align-items-center">
            <x-primary-button>Guardar contrasena</x-primary-button>

            <x-action-message class="ml-3" on="password-updated">
                Guardado.
            </x-action-message>
        </div>
    </form>
</section>
