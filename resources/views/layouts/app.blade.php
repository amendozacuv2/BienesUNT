@extends('adminlte::page')

@section('title', trim(strip_tags($title ?? config('app.name', 'Laravel'))))

@section('content_header')
    @isset($header)
        {{ $header }}
    @elseif (! empty($headerTitle))
        <h1 class="m-0 text-dark">{{ $headerTitle }}</h1>
    @endisset
@stop

@section('content')
    {{ $slot }}
@stop

@section('footer')
    <div class="float-right d-none d-sm-inline">
        LÁNZALO CODE
    </div>

    <strong>&copy; {{ now()->year }} LC </strong> Todos los derechos reservados.
@stop

@section('js')
    <script>
        (() => {
            const showNotification = (notification) => {
                if (! window.Swal || ! notification?.message) {
                    return;
                }

                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: notification.icon,
                    title: notification.message,
                    showConfirmButton: false,
                    showCloseButton: true,
                    timer: 3500,
                    timerProgressBar: true,
                });
            };

            const registerLivewireListener = () => {
                if (window.appNotificationsInitialized || ! window.Livewire) {
                    return;
                }

                Livewire.on('app-notify', showNotification);
                window.appNotificationsInitialized = true;
            };

            if (window.Livewire) {
                registerLivewireListener();
            } else {
                document.addEventListener('livewire:init', registerLivewireListener, { once: true });
            }

            showNotification(@json(session('notification')));
        })();
    </script>
@stop
