<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'UNIFRANZ Stream' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
@php
    $public = $public ?? false;
    $user = auth()->user();
    $role = $user?->role?->slug;
    $dashboardRoute = match ($role) {
        'administrador' => 'admin.dashboard',
        'docente' => 'teacher.dashboard',
        'estudiante' => 'student.dashboard',
        default => null,
    };
    $navigation = $public || ! $dashboardRoute ? [] : [
        ['label' => 'Mi panel', 'route' => $dashboardRoute],
        ['label' => 'Portal público', 'route' => 'landing'],
    ];
@endphp

<div class="relative min-h-screen overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,124,0,0.12),transparent_25%)]"></div>

    @if ($public)
        <header class="relative z-10 px-6 py-6 lg:px-10">
            <nav class="mx-auto flex max-w-7xl items-center justify-between rounded-full border border-white/60 bg-white/75 px-6 py-4 shadow-[0_14px_40px_rgba(17,17,17,0.06)] backdrop-blur-xl">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#111111] text-sm font-bold text-white">UF</span>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">UNIFRANZ</p>
                        <p class="text-sm text-slate-500">Academic Streaming Platform</p>
                    </div>
                </a>
                <div class="flex items-center gap-3">
                    <a href="#conocer-mas" class="hidden text-sm font-semibold text-slate-600 md:inline-flex">Conocer más</a>
                    <a href="{{ route('login') }}" class="btn-primary">Iniciar sesión</a>
                </div>
            </nav>
        </header>

        <main class="relative z-10">
            @yield('content')
        </main>
    @else
        <div class="relative z-10 lg:grid lg:min-h-screen lg:grid-cols-[290px_minmax(0,1fr)]">
            <aside class="hidden border-r border-white/60 bg-[#f5efe7]/80 px-6 py-8 backdrop-blur-xl lg:flex lg:flex-col">
                <a href="{{ route('dashboard') }}" class="mb-10 flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#111111] text-sm font-bold text-white">UF</span>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">UNIFRANZ</p>
                        <p class="text-sm text-slate-500">Streaming académico</p>
                    </div>
                </a>

                <div class="surface-panel p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Navegación</p>
                    <div class="mt-4 space-y-2">
                        @foreach ($navigation as $item)
                            <a href="{{ route($item['route']) }}" @class([
                                'sidebar-link',
                                'sidebar-link-active' => request()->routeIs($item['route']),
                            ])>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="surface-dark mt-6 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-orange-200/80">Acceso</p>
                    <p class="mt-3 text-lg font-semibold">{{ $user?->role?->name }}</p>
                    <p class="mt-2 text-sm text-white/70">Control segmentado por rol con acceso seguro y cuentas institucionales.</p>
                </div>

                <div class="mt-auto rounded-[28px] border border-dashed border-slate-300/80 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Cuenta</p>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-bold text-white" style="background-color: {{ $user?->avatar_color ?? '#F57C00' }}">
                            {{ strtoupper(substr($user?->name ?? 'UF', 0, 2)) }}
                        </span>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $user?->name }}</p>
                            <p class="text-sm text-slate-500">{{ $user?->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen flex-col">
                <header class="px-6 py-6 lg:px-8">
                    <div class="surface-panel flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-orange-600">{{ $eyebrow ?? 'Panel principal' }}</p>
                            <h1 class="mt-2 text-2xl font-semibold text-[#111111]">{{ $pageTitle ?? 'UNIFRANZ Stream' }}</h1>
                            @isset($pageDescription)
                                <p class="mt-1 text-sm text-slate-500">{{ $pageDescription }}</p>
                            @endisset
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('landing') }}" class="btn-secondary">Ver landing</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary">Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-6 pb-8 lg:px-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-6 rounded-3xl border border-orange-200 bg-orange-50 px-5 py-4 text-sm text-orange-700">
                            {{ session('status') }}
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
                </main>
            </div>
        </div>
    @endif
</div>
</body>
</html>
