@extends('layouts.app', [
    'title' => 'Dashboard docente | UNIFRANZ Stream',
    'eyebrow' => 'Panel docente',
    'pageTitle' => 'Gestionar transmisiones académicas',
    'pageDescription' => 'Programa clases, activa sesiones y revisa grabaciones de tus materias.',
])

@section('content')
    <div class="grid gap-8">
        <section class="grid gap-5 md:grid-cols-3">
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Clases totales</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['total'] }}</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Activas</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['live'] }}</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Grabaciones</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['recordings'] }}</p>
            </article>
        </section>

        <section class="grid gap-8 xl:grid-cols-[0.88fr_1.12fr]">
            <div class="surface-panel p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Nueva reunión</p>
                <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Programar clase</h2>

                <form action="{{ route('teacher.reuniones.store') }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label for="title" class="field-label">Título</label>
                        <input id="title" name="title" type="text" class="field-input" value="{{ old('title') }}"
                            required minlength="5" maxlength="120" autocomplete="off"
                            placeholder="Ej. Diseño de sistemas distribuidos">
                    </div>
                    <div>
                        <label for="description" class="field-label">Descripción</label>
                        <textarea id="description" name="description" rows="4" class="field-input" maxlength="1500"
                            placeholder="Objetivos, dinámica o enfoque de la clase">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="scheduled_at" class="field-label">Fecha y hora</label>
                            <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="field-input"
                                value="{{ old('scheduled_at', now()->addDay()->format('Y-m-d\TH:i')) }}"
                                min="{{ now()->format('Y-m-d\TH:i') }}" step="60" required>
                        </div>
                        <div>
                            <label for="duration_minutes" class="field-label">Duración</label>
                            <input id="duration_minutes" name="duration_minutes" type="number" min="30" max="240"
                                step="5" class="field-input" value="{{ old('duration_minutes', 90) }}" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full">Crear reunión</button>
                </form>
            </div>

            <div class="surface-panel p-7">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Sesiones</p>
                        <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Clases activas y recientes</h2>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($meetings as $meeting)
                        <article class="rounded-[28px] border border-slate-100 bg-white p-5">
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <x-status-badge :status="$meeting->estado" />
                                        <span class="text-sm text-slate-500">{{ optional($meeting->scheduled_at)->format('d M · H:i') }}</span>
                                    </div>
                                    <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ $meeting->title }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">{{ $meeting->participantes_count }} participantes registrados · código {{ $meeting->access_code }}</p>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('reuniones.show', $meeting) }}" class="btn-secondary">Ver</a>
                                    @if ($meeting->estado !== 'en_vivo')
                                        <form action="{{ route('teacher.reuniones.update', $meeting) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="en_vivo">
                                            <button type="submit" class="btn-primary">Iniciar</button>
                                        </form>
                                    @endif
                                    @if ($meeting->estado !== 'finalizada')
                                        <form action="{{ route('teacher.reuniones.update', $meeting) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="finalizada">
                                            <button type="submit" class="btn-secondary">Finalizar</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('teacher.reuniones.destroy', $meeting) }}" method="POST"
                                        onsubmit="return confirm('Se eliminara esta reunion y sus registros asociados. Continuar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-[24px] bg-slate-50 p-5 text-sm text-slate-500">Todavía no has programado clases.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="surface-panel p-7">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Grabaciones</p>
            <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Repositorio reciente</h2>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-slate-400">
                        <tr>
                            <th class="pb-4 font-medium">Título</th>
                            <th class="pb-4 font-medium">Clase</th>
                            <th class="pb-4 font-medium">Publicado</th>
                            <th class="pb-4 font-medium">Duración</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recordings as $recording)
                            <tr>
                                <td class="py-4 font-semibold text-slate-900">{{ $recording->title }}</td>
                                <td class="py-4 text-slate-500">{{ $recording->reunion?->title }}</td>
                                <td class="py-4 text-slate-500">{{ optional($recording->published_at)->format('d M Y') }}</td>
                                <td class="py-4 text-slate-500">{{ $recording->duration_minutes }} min</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-slate-500">No hay grabaciones disponibles todavía.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
