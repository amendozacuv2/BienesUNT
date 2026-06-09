<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }
}; ?>

<x-slot name="authHeader">
    Iniciar sesion
</x-slot>

<div>
    <form wire:submit="login">
        <div class="input-group mb-3">
            <input
                type="email"
                wire:model="form.email"
                name="email"
                class="form-control @error('form.email') is-invalid @enderror"
                placeholder="Correo electronico"
                autocomplete="username"
                autofocus
            >

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('form.email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="input-group mb-3">
            <input
                type="password"
                wire:model="form.password"
                name="password"
                class="form-control @error('form.password') is-invalid @enderror"
                placeholder="Contrasena"
                autocomplete="current-password"
            >

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('form.password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-7">
                <div class="icheck-primary">
                    <input type="checkbox" wire:model="form.remember" name="remember" id="remember">
                    <label for="remember">Recordarme</label>
                </div>
            </div>

            <div class="col-5">
                <button type="submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                    <span class="fas fa-sign-in-alt"></span>
                    Ingresar
                </button>
            </div>
        </div>
    </form>
</div>
