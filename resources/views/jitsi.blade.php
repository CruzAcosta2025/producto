@extends('layouts.app')

@section('title', 'Sala Jitsi')

@section('content')
<div class="flex flex-col h-[calc(100vh-4rem)]">
    <!-- Título -->
    <div class="p-4">
        <h2 class="font-semibold text-2xl text-indigo-600 dark:text-indigo-400 mb-2">
            {{ __('Reunión Jitsi') }}
        </h2>
    </div>

    <!-- Contenedor de Jitsi -->
    <style>
        #jitsi-container {
            height: 90vh;
            /* Altura igual al alto del viewport */
            width: 100%;
            /* Asegura el ancho completo */
            align-items: center;
        }
    </style>
    <div id="jitsi-container" class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <!-- Aquí se renderiza Jitsi -->
    </div>
</div>

<script src="https://meet.jit.si/external_api.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const domain = "meet.jit.si";
        const options = {
            roomName: "{{ $roomName }}", // Nombre de la sala generado
            width: "100%",
            height: "100%",
            parentNode: document.querySelector('#jitsi-container'),
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'hangup', 'chat', 'desktop', 'fullscreen'
                ]
            }
        };

        // Inicializar JitsiMeetExternalAPI
        const api = new JitsiMeetExternalAPI(domain, options);

        // Detectar el evento "hangup" (cuando el usuario cuelga)
        api.addEventListener('hangup', () => {
            window.location.href = "{{ url('/dashboard') }}";
        });

        // También manejar el evento readyToClose como respaldo
        api.addEventListener('readyToClose', () => {
            window.location.href = "{{ url('/dashboard') }}";
        });
    });
</script>

@endsection