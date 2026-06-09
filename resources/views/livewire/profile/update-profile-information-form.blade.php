<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <div class="mb-4">
        <h3 class="card-title font-weight-bold">Informacion del perfil</h3>
        <p class="text-muted mb-0">Actualiza tu nombre y correo electronico.</p>
    </div>

    <form wire:submit="updateProfileInformation">
        <div class="form-group">
            <x-input-label for="name" :value="'Nombre'" />
            <x-text-input wire:model="name" id="name" name="name" type="text" required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div class="form-group">
            <x-input-label for="email" :value="'Correo electronico'" />
            <x-text-input wire:model="email" id="email" name="email" type="email" required autocomplete="username" />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="alert alert-warning mt-3 mb-0">
                    <p class="mb-2">Tu correo electronico aun no ha sido verificado.</p>
                    <button wire:click.prevent="sendVerification" class="btn btn-warning btn-sm">
                        Reenviar verificacion
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="text-success small mt-2">
                        Se envio un nuevo enlace de verificacion a tu correo.
                    </div>
                @endif
            @endif
        </div>

        <div class="d-flex align-items-center">
            <x-primary-button>Guardar cambios</x-primary-button>

            <x-action-message class="ml-3" on="profile-updated">
                Guardado.
            </x-action-message>
        </div>
    </form>
</section>
