@extends('layouts.app', ['title' => 'UNIFRANZ Stream', 'public' => true])

@section('content')
    <section class="px-6 pb-12 pt-6 lg:px-10 lg:pb-20">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="surface-panel p-8 md:p-12">
                <span class="eyebrow">Plataforma cerrada para UNIFRANZ</span>
                <h1 class="mt-6 max-w-3xl text-4xl font-semibold tracking-tight text-[#111111] md:text-6xl">
                    Streaming académico para clases, reuniones y revisión de grabaciones.
                </h1>
                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 md:text-lg">
                    UNIFRANZ Stream combina la claridad operativa de un dashboard universitario con la fluidez visual de una plataforma de transmisiones en vivo.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('login') }}" class="btn-primary">Iniciar sesión</a>
                    <a href="#conocer-mas" class="btn-secondary">Conocer más</a>
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    <div class="rounded-[24px] bg-[var(--brand-50)] p-5">
                        <p class="text-3xl font-semibold text-[#111111]">{{ $stats['usuarios'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">Usuarios institucionales</p>
                    </div>
                    <div class="rounded-[24px] bg-white p-5 ring-1 ring-slate-100">
                        <p class="text-3xl font-semibold text-[#111111]">{{ $stats['reuniones'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">Clases y reuniones</p>
                    </div>
                    <div class="rounded-[24px] bg-white p-5 ring-1 ring-slate-100">
                        <p class="text-3xl font-semibold text-[#111111]">{{ $stats['grabaciones'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">Grabaciones publicadas</p>
                    </div>
                </div>
            </div>

            <div class="hero-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.24em] text-orange-100/80">Vista destacada</p>
                        <h2 class="mt-3 text-3xl font-semibold">Inicio de transmisiones</h2>
                    </div>
                    <span class="rounded-full border border-white/15 px-4 py-2 text-xs uppercase tracking-[0.24em] text-orange-100/80">Cerrado</span>
                </div>

                <div class="mt-8 space-y-4">
                    @forelse ($featuredMeetings as $meeting)
                        <article class="rounded-[28px] border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <x-status-badge :status="$meeting->estado" class="border-white/15 bg-white/10 text-white" />
                                    <h3 class="mt-4 text-xl font-semibold">{{ $meeting->title }}</h3>
                                    <p class="mt-2 text-sm text-white/70">{{ $meeting->docente?->name }}</p>
                                </div>
                                <div class="rounded-2xl bg-white/10 px-4 py-3 text-right text-sm">
                                    <p>{{ optional($meeting->scheduled_at)->format('d M') }}</p>
                                    <p class="text-white/65">{{ optional($meeting->scheduled_at)->format('H:i') }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[28px] border border-white/10 bg-white/10 p-6 text-sm text-white/70">
                            No hay clases destacadas todavía. Ejecuta los seeders para poblar el panel inicial.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section id="conocer-mas" class="px-6 py-12 lg:px-10 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <span class="eyebrow">Beneficios</span>
            <div class="mt-5 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <h2 class="section-heading max-w-3xl">Diseñado para una operación académica seria, moderna y administrable.</h2>
                <p class="max-w-xl text-sm leading-7 text-slate-500">
                    Cada módulo responde a una necesidad concreta: acceso institucional seguro, gestión por roles y consumo de contenido académico en vivo o bajo demanda.
                </p>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                <article class="surface-panel p-7">
                    <p class="text-sm font-semibold text-orange-600">Acceso institucional</p>
                    <h3 class="mt-3 text-2xl font-semibold">Solo @unifranz.edu.bo</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500">Validación en frontend y backend para restringir ingresos a cuentas institucionales activas.</p>
                </article>
                <article class="surface-panel p-7">
                    <p class="text-sm font-semibold text-orange-600">Paneles por rol</p>
                    <h3 class="mt-3 text-2xl font-semibold">Docente, estudiante y admin</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500">Cada perfil recibe una experiencia orientada a su tarea real dentro del ecosistema universitario.</p>
                </article>
                <article class="surface-panel p-7">
                    <p class="text-sm font-semibold text-orange-600">Ecosistema de clases</p>
                    <h3 class="mt-3 text-2xl font-semibold">Live, agenda y grabaciones</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500">La plataforma organiza sesiones activas, programadas y finalizadas con continuidad entre formatos.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="px-6 py-12 lg:px-10 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="surface-dark p-8">
                <span class="eyebrow border-orange-400/20 bg-orange-500/10 text-orange-100">Cómo funciona</span>
                <h2 class="mt-5 text-3xl font-semibold">Un flujo simple para sostener operación académica real.</h2>
                <div class="mt-8 space-y-4">
                    <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-semibold">1. Acceso seguro</p>
                        <p class="mt-2 text-sm text-white/70">El usuario inicia sesión con su cuenta institucional y entra al entorno según su rol.</p>
                    </div>
                    <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-semibold">2. Gestión o consumo</p>
                        <p class="mt-2 text-sm text-white/70">Docentes programan y gestionan clases. Estudiantes exploran sesiones y grabaciones.</p>
                    </div>
                    <div class="rounded-[24px] border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-semibold">3. Supervisión institucional</p>
                        <p class="mt-2 text-sm text-white/70">Administración central monitorea usuarios, reuniones y disponibilidad de contenidos.</p>
                    </div>
                </div>
            </div>

            <div class="surface-panel p-8">
                <h2 class="section-heading">Interfaz pensada para claridad y jerarquía visual.</h2>
                <div class="mt-8 grid gap-5 md:grid-cols-2">
                    <div class="rounded-[28px] bg-[var(--brand-50)] p-6">
                        <p class="text-sm font-semibold text-orange-700">Landing informativa</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Presentación institucional con CTA claros, beneficios y propuesta de valor tecnológica.</p>
                    </div>
                    <div class="rounded-[28px] bg-white p-6 ring-1 ring-slate-100">
                        <p class="text-sm font-semibold text-orange-700">Autenticación limpia</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Pantallas centradas, mensajes de error elegantes y recuperación de contraseña incorporada.</p>
                    </div>
                    <div class="rounded-[28px] bg-white p-6 ring-1 ring-slate-100">
                        <p class="text-sm font-semibold text-orange-700">Catálogo académico</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Clases en vivo, programadas y grabadas presentadas como contenido consumible.</p>
                    </div>
                    <div class="rounded-[28px] bg-[var(--brand-50)] p-6">
                        <p class="text-sm font-semibold text-orange-700">Control operativo</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Administración institucional y acciones concretas para crear o actualizar recursos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="px-6 pb-10 pt-6 lg:px-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 rounded-[28px] border border-white/60 bg-white/75 px-6 py-6 text-sm text-slate-500 shadow-[0_14px_40px_rgba(17,17,17,0.06)] backdrop-blur-xl md:flex-row md:items-center md:justify-between">
            <p>UNIFRANZ Stream. Plataforma institucional para clases en vivo y grabaciones académicas.</p>
            <p>Solo accesible con cuentas @unifranz.edu.bo.</p>
        </div>
    </footer>
@endsection
