<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Videollamada | {{ $reunion->title }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#060606] text-white antialiased">
<div class="flex min-h-screen flex-col">
    <header class="border-b border-white/10 bg-black/60 px-4 py-3 backdrop-blur">
        <div class="mx-auto flex w-full max-w-[1600px] flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-orange-200/80">Sala en vivo</p>
                <h1 class="truncate text-lg font-semibold">{{ $reunion->title }}</h1>
                <p class="text-sm text-white/70">{{ $reunion->docente?->name }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($isTeacherOwner)
                    <form action="{{ route('teacher.reuniones.update', $reunion) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="finalizada">
                        <button type="submit" class="btn-secondary">Finalizar clase</button>
                    </form>
                @endif
                <a href="{{ route('reuniones.show', $reunion) }}" class="btn-ghost">Volver al detalle</a>
            </div>
        </div>
    </header>

    <main class="flex-1 px-3 py-3 sm:px-4 sm:py-4">
        <div class="mx-auto h-[calc(100vh-112px)] w-full max-w-[1600px] overflow-hidden rounded-2xl border border-white/10 bg-black">
            <div id="jitsi-live-container" class="h-full w-full"></div>
        </div>
    </main>
</div>

<script src="https://meet.jit.si/external_api.js"></script>
<script>
    (function () {
        const container = document.getElementById('jitsi-live-container');
        if (!container || typeof window.JitsiMeetExternalAPI !== 'function') {
            return;
        }

        const domain = @json($jitsiDomain);
        const roomName = @json($jitsiRoom);
        if (!roomName) {
            return;
        }

        const api = new window.JitsiMeetExternalAPI(domain, {
            roomName: roomName,
            parentNode: container,
            width: '100%',
            height: '100%',
            configOverwrite: {
                prejoinConfig: {
                    enabled: false,
                },
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                disableDeepLinking: true,
            },
            interfaceConfigOverwrite: {
                MOBILE_APP_PROMO: false,
                HIDE_DEEP_LINKING_LOGO: true,
            },
            userInfo: {
                displayName: @json($userDisplayName),
                email: @json($userEmail),
            },
        });

        const leaveRedirectUrl = @json($leaveRedirectUrl);

        api.addListener('videoConferenceLeft', () => {
            window.location.assign(leaveRedirectUrl);
        });

        api.addListener('readyToClose', () => {
            window.location.assign(leaveRedirectUrl);
        });

        window.addEventListener('beforeunload', () => {
            if (api && typeof api.dispose === 'function') {
                api.dispose();
            }
        });
    })();
</script>
</body>
</html>
