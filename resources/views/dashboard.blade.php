<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0 text-dark">Inicio</h1>
    </x-slot>

    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ auth()->user()->name }}</h3>
                    <p>Sesion activa en el panel de bienes.</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('profile') }}" class="small-box-footer">
                    Administrar perfil <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-8 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bienvenido</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2">La integracion base con AdminLTE ya esta activa para la navegacion, autenticacion y panel principal.</p>
                    <p class="mb-0">Desde aqui podemos seguir montando modulos de inventario, usuarios, reportes y catalogos sobre una estructura administrativa consistente.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
