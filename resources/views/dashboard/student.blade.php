@extends('layouts.app', [
    'title' => 'Dashboard estudiante | UNIFRANZ Stream',
    'eyebrow' => 'Panel estudiante',
    'pageTitle' => 'Explorar clases y grabaciones',
    'pageDescription' => 'Accede a sesiones en vivo, agenda académica y contenido reciente.',
])

@section('content')
    <div class="grid gap-8">
        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="surface-dark p-7">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.24em] text-orange-100/80">En vivo ahora</p>
                        <h2 class="mt-3 text-3xl font-semibold">Clases activas para ingresar de inmediato</h2>
                    </div>
                    <span class="rounded-full border border-white/10 px-4 py-2 text-xs uppercase tracking-[0.24em] text-white/60">{{ $liveMeetings->count() }} disponibles</span>
                </div>

                <div class="mt-8 grid gap-4">
                    @forelse ($liveMeetings as $meeting)
                        <article class="rounded-[28px] border border-white/10 bg-white/5 p-5">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <x-status-badge :status="$meeting->estado" class="border-white/10 bg-red-500/10 text-red-100" />
                                    <h3 class="mt-4 text-xl font-semibold">{{ $meeting->title }}</h3>
                                    <p class="mt-2 text-sm text-white/70">{{ $meeting->docente?->name }} · {{ optional($meeting->started_at)->format('H:i') }}</p>
                                </div>
                                <a href="{{ route('reuniones.show', $meeting) }}" class="btn-ghost">Entrar</a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[28px] border border-white/10 bg-white/5 p-6 text-sm text-white/70">
                            No hay transmisiones activas en este momento.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="surface-panel p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Historial</p>
                <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Actividad reciente</h2>
                <div class="mt-6 space-y-4">
                    @forelse ($historyMeetings as $meeting)
                        <a href="{{ route('reuniones.show', $meeting) }}" class="block rounded-[24px] border border-slate-100 bg-white p-5 transition hover:border-orange-200">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $meeting->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $meeting->docente?->name }}</p>
                                </div>
                                <x-status-badge :status="$meeting->estado" />
                            </div>
                        </a>
                    @empty
                        <p class="rounded-[24px] bg-slate-50 p-5 text-sm text-slate-500">Tu historial aparecerá a medida que ingreses a clases.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="surface-panel p-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Programadas</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Próximas clases</h2>
                </div>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($scheduledMeetings as $meeting)
                    <article class="overflow-hidden rounded-[28px] border border-slate-100 bg-white">
                        <div class="bg-gradient-to-br {{ $meeting->cover_gradient ?? 'from-orange-500 via-zinc-800 to-slate-950' }} p-6 text-white">
                            <x-status-badge :status="$meeting->estado" class="border-white/10 bg-white/10 text-white" />
                            <h3 class="mt-4 text-2xl font-semibold">{{ $meeting->title }}</h3>
                            <p class="mt-2 text-sm text-white/75">{{ $meeting->docente?->name }}</p>
                        </div>
                        <div class="p-6">
                            <p class="text-sm leading-7 text-slate-500">{{ \Illuminate\Support\Str::limit($meeting->description, 120) }}</p>
                            <div class="mt-5 flex items-center justify-between text-sm text-slate-500">
                                <span>{{ optional($meeting->scheduled_at)->format('d M · H:i') }}</span>
                                <span>{{ $meeting->duration_minutes }} min</span>
                            </div>
                            <a href="{{ route('reuniones.show', $meeting) }}" class="btn-secondary mt-6 w-full">Ver sesión</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="surface-panel p-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Grabaciones recientes</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Repasa el contenido cuando lo necesites</h2>
                </div>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($recentRecordings as $recording)
                    <article class="rounded-[28px] border border-slate-100 bg-white p-6">
                        <p class="text-sm font-semibold text-orange-600">{{ optional($recording->published_at)->format('d M Y') }}</p>
                        <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $recording->title }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $recording->reunion?->docente?->name }}</p>
                        <p class="mt-4 text-sm leading-7 text-slate-500">{{ \Illuminate\Support\Str::limit($recording->description, 100) }}</p>
                        <a href="{{ route('reuniones.show', $recording->reunion) }}" class="btn-secondary mt-6 w-full">Abrir clase</a>
                    </article>
                @empty
                    <p class="rounded-[24px] bg-slate-50 p-5 text-sm text-slate-500">Aún no hay grabaciones disponibles.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
