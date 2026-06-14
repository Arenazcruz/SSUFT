@extends('layouts.app', [
    'title' => $reunion->title.' | UNIFRANZ Stream',
    'eyebrow' => 'Sala académica',
    'pageTitle' => $reunion->title,
    'pageDescription' => 'Vista principal de clase, información operativa y actividad reciente.',
])

@section('content')
    <div class="grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="grid gap-8">
            <div class="surface-dark overflow-hidden p-0">
                <div class="bg-gradient-to-br {{ $reunion->cover_gradient ?? 'from-orange-500 via-zinc-800 to-slate-950' }} p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <x-status-badge :status="$reunion->estado" class="border-white/10 bg-white/10 text-white" />
                            <h2 class="mt-4 text-3xl font-semibold">{{ $reunion->title }}</h2>
                            <p class="mt-2 text-sm text-white/70">{{ $reunion->docente?->name }} · {{ optional($reunion->scheduled_at)->format('d M Y · H:i') }}</p>
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn-ghost">Volver al panel</a>
                    </div>
                </div>
                <div class="p-8">
                    <div class="rounded-[28px] border border-white/10 bg-white/5 p-8 text-center">
                        <p class="text-sm uppercase tracking-[0.24em] text-orange-100/70">Streaming académico</p>
                        @if ($canJoinLiveRoom)
                            <h3 class="mt-4 text-2xl font-semibold">Clase en vivo</h3>
                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/70">
                                La videollamada está activa. Ábrela en una ventana dedicada para mantener este panel como centro de control.
                            </p>
                            <div class="mx-auto mt-6 max-w-4xl rounded-2xl border border-white/15 bg-white/5 p-5 text-left">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="{{ $liveRoomUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn-ghost">
                                        Abrir videollamada
                                    </a>
                                    @if ($isTeacherOwner)
                                        <form action="{{ route('teacher.reuniones.update', $reunion) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="finalizada">
                                            <button type="submit" class="btn-secondary">
                                                Finalizar clase
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @elseif ($reunion->estado === 'programada')
                            <h3 class="mt-4 text-2xl font-semibold">Clase programada</h3>
                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/70">
                                Esta clase aún no inició. La videollamada estará disponible cuando el docente la ponga en vivo.
                            </p>
                        @elseif ($streamAccessClosed)
                            <h3 class="mt-4 text-2xl font-semibold">Transmisión cerrada</h3>
                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/70">
                                Esta sesión fue {{ $reunion->estado === 'cancelada' ? 'cancelada por el docente' : 'finalizada por el docente' }} y ya no admite acceso en tiempo real.
                            </p>
                        @else
                            <h3 class="mt-4 text-2xl font-semibold">Sala en preparación</h3>
                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/70">
                                La clase está en vivo, pero todavía no tiene una sala Jitsi disponible.
                            </p>
                        @endif
                        <div class="mt-8 grid gap-4 md:grid-cols-3">
                            <div class="rounded-[24px] border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.24em] text-white/50">Participantes</p>
                                <p class="mt-3 text-3xl font-semibold">{{ $reunion->participantes->count() }}</p>
                            </div>
                            <div class="rounded-[24px] border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.24em] text-white/50">Duración</p>
                                <p class="mt-3 text-3xl font-semibold">{{ $reunion->duration_minutes }} min</p>
                            </div>
                            <div class="rounded-[24px] border border-white/10 bg-white/5 p-4">
                                <p class="text-xs uppercase tracking-[0.24em] text-white/50">Código</p>
                                <p class="mt-3 text-3xl font-semibold">{{ $reunion->access_code }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface-panel p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Grabaciones asociadas</p>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @forelse ($reunion->grabaciones as $recording)
                        <article class="rounded-[24px] bg-slate-50 p-5">
                            <p class="text-sm font-semibold text-orange-600">{{ optional($recording->published_at)->format('d M Y') }}</p>
                            <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $recording->title }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-500">{{ $recording->description }}</p>
                        </article>
                    @empty
                        <p class="rounded-[24px] bg-slate-50 p-5 text-sm text-slate-500">Esta clase todavía no tiene grabaciones publicadas.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="grid gap-8">
            <div class="surface-panel p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Chat de reunión</p>
                <div class="mt-6 space-y-4">
                    @forelse ($reunion->mensajes as $message)
                        <div class="rounded-[24px] bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-semibold text-slate-900">{{ $message->user?->name }}</p>
                                <p class="text-xs text-slate-400">{{ optional($message->sent_at)->format('H:i') }}</p>
                            </div>
                            <p class="mt-2 text-sm leading-7 text-slate-500">{{ $message->message }}</p>
                        </div>
                    @empty
                        <p class="rounded-[24px] bg-slate-50 p-5 text-sm text-slate-500">No hay mensajes recientes para esta sesión.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-panel p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Otras clases</p>
                <div class="mt-6 space-y-4">
                    @foreach ($relatedMeetings as $meeting)
                        <a href="{{ route('reuniones.show', $meeting) }}" class="block rounded-[24px] border border-slate-100 bg-white p-5 transition hover:border-orange-200">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $meeting->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $meeting->docente?->name }}</p>
                                </div>
                                <x-status-badge :status="$meeting->estado" />
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
@endsection
