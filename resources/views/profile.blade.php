<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0 text-dark">Perfil</h1>
    </x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <livewire:profile.update-password-form />
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-danger card-outline">
                <div class="card-body">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
