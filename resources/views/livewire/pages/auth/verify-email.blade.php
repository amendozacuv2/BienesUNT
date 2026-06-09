<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<x-slot name="authHeader">
    Verificar correo
</x-slot>

<div>
    <p class="login-box-msg">
        Antes de continuar, verifica tu direccion de correo desde el enlace enviado.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">
            Se envio un nuevo enlace de verificacion.
        </div>
    @endif

    <button wire:click="sendVerification" type="button" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
        Reenviar verificacion
    </button>

    <button wire:click="logout" type="button" class="btn btn-block btn-default mt-2">
        Cerrar sesion
    </button>
</div>
