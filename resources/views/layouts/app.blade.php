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
    $profilePhotoUrl = $user?->foto_perfil
        ? route('profile.photo', ['v' => optional($user->updated_at)->timestamp])
        : null;
    $dashboardRoute = match ($role) {
        'superadministrador' => 'superadmin.dashboard',
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

<div class="relative min-h-screen">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,124,0,0.12),transparent_25%)]"></div>

    @if ($public)
        <header class="relative z-10 px-6 py-6 lg:px-10">
            <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 rounded-[28px] border border-white/60 bg-white/75 px-4 py-4 shadow-[0_14px_40px_rgba(17,17,17,0.06)] backdrop-blur-xl sm:rounded-full sm:px-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#111111] text-sm font-bold text-white">UF</span>
                    <div class="min-w-0">
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">UNIFRANZ</p>
                        <p class="text-sm text-slate-500">Academic Streaming Platform</p>
                    </div>
                </a>
                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto sm:justify-end">
                    <a href="#conocer-mas" class="hidden text-sm font-semibold text-slate-600 md:inline-flex">Conocer más</a>
                    <a href="{{ route('login') }}" class="btn-primary w-full sm:w-auto">Iniciar sesión</a>
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
                    <a href="{{ route('profile.edit') }}" class="mt-4 flex items-center gap-3 rounded-2xl px-2 py-2 transition hover:bg-white hover:shadow-sm">
                        @if ($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="Foto de perfil" class="h-11 w-11 rounded-2xl border border-white object-cover">
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-bold text-white" style="background-color: {{ $user?->avatar_color ?? '#F57C00' }}">
                                {{ strtoupper(substr($user?->name ?? 'UF', 0, 2)) }}
                            </span>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-900">{{ $user?->name }}</p>
                            <p class="text-sm text-slate-500">{{ $user?->email }}</p>
                        </div>
                    </a>
                </div>
            </aside>

            <div class="flex min-h-screen flex-col">
                <div class="px-4 pt-4 sm:px-6 lg:hidden">
                    <details class="surface-panel overflow-hidden">
                        <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 text-sm font-semibold text-slate-700">
                            Navegación y cuenta
                            <span class="text-xs uppercase tracking-[0.2em] text-orange-600">Menu</span>
                        </summary>
                        <div class="border-t border-slate-100 px-5 py-4">
                            <div class="space-y-2">
                                @foreach ($navigation as $item)
                                    <a href="{{ route($item['route']) }}" @class([
                                        'sidebar-link',
                                        'sidebar-link-active' => request()->routeIs($item['route']),
                                    ])>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                                <a href="{{ route('profile.edit') }}" @class([
                                    'sidebar-link',
                                    'sidebar-link-active' => request()->routeIs('profile.edit'),
                                ])>
                                    <span>Mi perfil</span>
                                </a>
                            </div>

                            <div class="mt-4 rounded-2xl border border-dashed border-slate-200 p-4">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Cuenta activa</p>
                                <p class="mt-2 font-semibold text-slate-900">{{ $user?->name }}</p>
                                <p class="mt-1 text-sm break-all text-slate-500">{{ $user?->email }}</p>
                            </div>
                        </div>
                    </details>
                </div>

                <header class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                    <div class="surface-panel flex flex-col gap-4 px-4 py-4 sm:px-6 sm:py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-orange-600">{{ $eyebrow ?? 'Panel principal' }}</p>
                            <h1 class="mt-2 text-2xl font-semibold text-[#111111]">{{ $pageTitle ?? 'UNIFRANZ Stream' }}</h1>
                            @isset($pageDescription)
                                <p class="mt-1 text-sm text-slate-500">{{ $pageDescription }}</p>
                            @endisset
                        </div>
                        <div class="flex w-full flex-wrap items-center gap-3 md:w-auto md:justify-end">
                            <a href="{{ route('landing') }}" class="btn-secondary w-full sm:w-auto">Ver landing</a>
                            <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="btn-primary w-full sm:w-auto">Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-4 pb-6 sm:px-6 sm:pb-8 lg:px-8">
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
