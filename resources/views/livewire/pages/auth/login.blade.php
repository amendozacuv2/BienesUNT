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
        $this->form->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('profile', absolute: false), navigate: true);
    }
}; ?>

<x-slot name="authHeader">
    <div class="login-title">
        <span class="fas fa-lock"></span>
        <span>Iniciar sesión</span>
    </div>
</x-slot>

@push('css')
    <style>
        .login-minimal {
            padding-top: .25rem;
        }

        .login-minimal .login-intro {
            text-align: center;
            margin-bottom: 1.25rem;
        }

        .login-minimal .login-intro h5 {
            font-weight: 700;
            margin-bottom: .25rem;
            color: #343a40;
        }

        .login-minimal .login-intro p {
            margin-bottom: 0;
            font-size: .9rem;
            color: #6c757d;
        }

        .login-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            font-weight: 700;
        }

        .login-minimal .form-label {
            font-size: .85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: .35rem;
        }

        .login-minimal .input-group {
            border-radius: .75rem;
        }

        .login-minimal .form-control {
            height: 44px;
            border-radius: .75rem 0 0 .75rem;
            border-color: #dee2e6;
            font-size: .95rem;
        }

        .login-minimal .form-control:focus {
            border-color: #80bdff;
            box-shadow: none;
        }

        .login-minimal .input-group-text {
            min-width: 46px;
            justify-content: center;
            border-radius: 0 .75rem .75rem 0;
            background: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }

        .login-minimal .invalid-feedback {
            display: block;
            font-size: .82rem;
            margin-top: .35rem;
        }

        .login-minimal .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .login-minimal .remember-row label {
            margin-bottom: 0;
            font-size: .9rem;
            color: #495057;
            cursor: pointer;
        }

        .login-minimal .btn-login {
            height: 44px;
            border-radius: .75rem;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .login-minimal .login-footer-text {
            text-align: center;
            margin-top: 1rem;
            font-size: .8rem;
            color: #adb5bd;
        }
    </style>
@endpush

<div class="login-minimal">
    <div class="login-intro">
        <h5>Bienvenido</h5>
        <p>Ingrese sus credenciales para acceder al sistema.</p>
    </div>

    <form wire:submit.prevent="login" autocomplete="off">
        <div class="form-group mb-3">
            <label for="username" class="form-label">
                Usuario
            </label>

            <div class="input-group">
                <input
                    type="text"
                    wire:model="form.username"
                    id="username"
                    class="form-control @error('form.username') is-invalid @enderror"
                    placeholder="Ingrese su usuario"
                    autocomplete="username"
                    autofocus
                >

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
            </div>

            @error('form.username')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="password" class="form-label">
                Contraseña
            </label>

            <div class="input-group">
                <input
                    type="password"
                    wire:model="form.password"
                    id="password"
                    class="form-control @error('form.password') is-invalid @enderror"
                    placeholder="Ingrese su contraseña"
                    autocomplete="current-password"
                >

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>
            </div>

            @error('form.password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="remember-row">
            <div class="icheck-primary">
                <input
                    type="checkbox"
                    wire:model="form.remember"
                    name="remember"
                    id="remember"
                >

                <label for="remember">
                    Recordarme
                </label>
            </div>
        </div>

        <button
            type="submit"
            class="btn btn-block btn-login {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}"
            wire:loading.attr="disabled"
            wire:target="login"
        >
            <span wire:loading.remove wire:target="login">
                <span class="fas fa-sign-in-alt mr-1"></span>
                Ingresar
            </span>

            <span wire:loading wire:target="login">
                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                Validando...
            </span>
        </button>
    </form>

    <div class="login-footer-text">
        Sistema de Gestión de Bienes
    </div>
</div>
