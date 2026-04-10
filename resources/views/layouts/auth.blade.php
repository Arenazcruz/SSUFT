<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Acceso institucional | UNIFRANZ Stream' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,124,0,0.18),transparent_28%),linear-gradient(180deg,#fffdfa_0%,#f3ede6_100%)]"></div>

        <div class="relative z-10 grid w-full max-w-6xl overflow-hidden rounded-[36px] border border-white/60 bg-white/75 shadow-[0_30px_90px_rgba(17,17,17,0.12)] backdrop-blur-xl lg:grid-cols-[1.15fr_0.85fr]">
            <section class="hidden bg-[#111111] px-10 py-12 text-white lg:block">
                <span class="eyebrow border-orange-400/20 bg-orange-500/10 text-orange-200">UNIFRANZ Stream</span>
                <h1 class="mt-6 max-w-md text-4xl font-semibold leading-tight">Clases en vivo con experiencia institucional, segura y enfocada en rendimiento.</h1>
                <p class="mt-5 max-w-lg text-sm leading-7 text-white/70">
                    Acceso exclusivo para cuentas @unifranz.edu.bo, dashboards por rol, reuniones programadas y experiencia tipo streaming académico.
                </p>

                <div class="mt-10 space-y-4">
                    <div class="rounded-[28px] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-semibold">Acceso restringido</p>
                        <p class="mt-2 text-sm text-white/70">Solo cuentas institucionales con control de acceso por roles.</p>
                    </div>
                    <div class="rounded-[28px] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-semibold">Flujo académico</p>
                        <p class="mt-2 text-sm text-white/70">Docentes gestionan transmisiones. Estudiantes consumen clases y grabaciones.</p>
                    </div>
                </div>
            </section>

            <section class="px-6 py-8 sm:px-10 sm:py-12">
                <div class="mb-8 flex items-center justify-between">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#111111] text-sm font-bold text-white">UF</span>
                        <div>
                            <p class="font-display text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">UNIFRANZ</p>
                            <p class="text-sm text-slate-500">Streaming académico</p>
                        </div>
                    </a>
                    <a href="{{ route('landing') }}" class="text-sm font-semibold text-slate-500">Volver</a>
                </div>

                @if (session('status'))
                    <div class="mb-6 rounded-3xl border border-orange-200 bg-orange-50 px-5 py-4 text-sm text-orange-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-institutional-email]').forEach((input) => {
            input.addEventListener('input', (event) => {
                const value = event.target.value.trim().toLowerCase();
                const valid = value === '' || value.endsWith('@unifranz.edu.bo');
                event.target.setCustomValidity(valid ? '' : 'Solo se admiten correos @unifranz.edu.bo');
            });
        });
    </script>
</body>
</html>
