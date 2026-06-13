@extends('layouts.app', [
    'title' => 'Dashboard administrador | UNIFRANZ Stream',
    'eyebrow' => 'Panel administrador',
    'pageTitle' => 'Gobierno institucional de la plataforma',
    'pageDescription' => 'Administra usuarios, roles, reuniones y capacidad operativa del ecosistema.',
])

@php
    $meetingTotal = $meetings->count();
    $meetingScheduled = $meetings->where('estado', 'programada')->count();
    $meetingLive = $meetings->where('estado', 'en_vivo')->count();
    $meetingFinished = $meetings->where('estado', 'finalizada')->count();
    $activeUsers = $users->where('activo', true)->count();
    $inactiveUsers = $users->where('activo', false)->count();
@endphp

@section('content')
    <div class="admin-dashboard-shell" data-admin-dashboard>
        <section class="admin-dashboard-hero surface-dark" data-dashboard-panel>
            <div class="admin-dashboard-hero-copy">
                <p class="admin-dashboard-kicker">Centro de control</p>
                <h2 class="admin-dashboard-hero-title">Operacion academica, usuarios y clases en una sola vista.</h2>
                <p class="admin-dashboard-hero-text">
                    Supervisa el flujo institucional, filtra reuniones por estado y ajusta cuentas sin salir del panel.
                </p>

                <div class="admin-dashboard-highlight-grid">
                    <article class="admin-dashboard-highlight-card">
                        <span class="admin-dashboard-highlight-label">Usuarios activos</span>
                        <strong>{{ $activeUsers }}</strong>
                    </article>

                    <article class="admin-dashboard-highlight-card">
                        <span class="admin-dashboard-highlight-label">Clases en vivo</span>
                        <strong>{{ $meetingLive }}</strong>
                    </article>

                    <article class="admin-dashboard-highlight-card">
                        <span class="admin-dashboard-highlight-label">Reuniones programadas</span>
                        <strong>{{ $meetingScheduled }}</strong>
                    </article>
                </div>
            </div>

            <div class="admin-dashboard-hero-side">
                <div class="admin-dashboard-orbit">
                    <div class="admin-dashboard-orbit-core">
                        <span>Capacidad</span>
                        <strong>{{ $metrics['usuarios'] + $metrics['reuniones'] }}</strong>
                    </div>
                </div>

                <div class="admin-dashboard-mini-stats">
                    <article>
                        <span>Grabaciones</span>
                        <strong>{{ $metrics['grabaciones'] }}</strong>
                    </article>
                    <article>
                        <span>Inactivas</span>
                        <strong>{{ $inactiveUsers }}</strong>
                    </article>
                </div>
            </div>
        </section>

        <section class="surface-panel admin-dashboard-panel admin-dashboard-summary-band" data-dashboard-panel>
            <div class="admin-dashboard-summary-band-head">
                <div>
                    <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Resumen operativo</p>
                    <h2 class="admin-dashboard-section-title">Lectura rapida del sistema</h2>
                </div>
                <p class="admin-dashboard-summary-band-text">
                    Estado sintetico del sistema para revisar acceso, actividad y capacidad operativa sin bajar al resto
                    del panel.
                </p>
            </div>

            <div class="admin-dashboard-summary-band-body">
                <div class="admin-dashboard-summary-stack admin-dashboard-summary-stack-horizontal">
                    <article class="admin-dashboard-summary-card">
                        <span>Acceso</span>
                        <strong>{{ $activeUsers }}/{{ $users->count() }}</strong>
                        <p>Cuentas activas respecto al total institucional.</p>
                    </article>

                    <article class="admin-dashboard-summary-card">
                        <span>Actividad</span>
                        <strong>{{ $meetingLive }}</strong>
                        <p>Clases que requieren observacion inmediata.</p>
                    </article>

                    <article class="admin-dashboard-summary-card">
                        <span>Pipeline</span>
                        <strong>{{ $meetingScheduled }}</strong>
                        <p>Sesiones pendientes de ejecucion en agenda.</p>
                    </article>
                </div>

                <div class="admin-dashboard-timeline admin-dashboard-timeline-horizontal">
                    <article>
                        <span class="admin-dashboard-timeline-dot"></span>
                        <div>
                            <strong>Prioridad alta</strong>
                            <p>Validar reuniones en vivo y docentes asignados.</p>
                        </div>
                    </article>

                    <article>
                        <span class="admin-dashboard-timeline-dot"></span>
                        <div>
                            <strong>Prioridad media</strong>
                            <p>Revisar cuentas inactivas y roles con baja cobertura.</p>
                        </div>
                    </article>

                    <article>
                        <span class="admin-dashboard-timeline-dot"></span>
                        <div>
                            <strong>Prioridad estable</strong>
                            <p>Monitorear el crecimiento de grabaciones publicadas.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="admin-dashboard-metrics" data-dashboard-panel>
            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">Usuarios</span>
                <strong>{{ $metrics['usuarios'] }}</strong>
                <p>Base total de cuentas institucionales.</p>
            </article>

            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">Reuniones</span>
                <strong>{{ $metrics['reuniones'] }}</strong>
                <p>Clases registradas dentro del sistema.</p>
            </article>

            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">Grabaciones</span>
                <strong>{{ $metrics['grabaciones'] }}</strong>
                <p>Contenido disponible para consulta posterior.</p>
            </article>

            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">En vivo</span>
                <strong>{{ $metrics['live'] }}</strong>
                <p>Actividad concurrente monitoreada ahora.</p>
            </article>
        </section>

        <section class="admin-dashboard-grid">
            <div class="admin-dashboard-column">
                <section class="surface-panel admin-dashboard-panel" data-dashboard-panel>
                    <div class="admin-dashboard-panel-head">
                        <div>
                            <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Crear usuario</p>
                            <h2 class="admin-dashboard-section-title">Alta institucional</h2>
                        </div>
                    </div>

                    <form action="{{ route('admin.users.store') }}" method="POST" class="admin-dashboard-form">
                        @csrf

                        <div>
                            <label for="name" class="field-label">Nombre</label>
                            <input id="name" name="name" type="text" class="field-input" value="{{ old('name') }}"
                                minlength="3" maxlength="120" autocomplete="name"
                                required>
                        </div>

                        <div>
                            <label for="email" class="field-label">Correo institucional</label>
                            <input id="email" name="email" type="email" class="field-input"
                                value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$">
                        </div>

                        <div>
                            <label for="role_id" class="field-label">Rol</label>
                            <select id="role_id" name="role_id" class="field-select" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="admin-dashboard-form-grid">
                            <div>
                                <label for="password" class="field-label">Contrasena</label>
                                <div class="relative">
                                    <input id="password" name="password" type="password" class="field-input pr-24"
                                        minlength="8" required>
                                    <button type="button"
                                        data-password-toggle-button
                                        data-target="password"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                                        Mostrar
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="password_confirmation" class="field-label">Confirmar</label>
                                <div class="relative">
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        class="field-input pr-24" minlength="8" required>
                                    <button type="button"
                                        data-password-toggle-button
                                        data-target="password_confirmation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                                        Mostrar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <label class="admin-dashboard-toggle-row">
                            <input type="checkbox" name="activo" value="1"
                                class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200" checked>
                            Activar cuenta al crearla
                        </label>

                        <button type="submit" class="btn-primary w-full">Crear usuario</button>
                    </form>
                </section>

            </div>

            <section class="surface-panel admin-dashboard-panel admin-dashboard-panel-tall" data-dashboard-panel>
                <div class="admin-dashboard-panel-head">
                    <div>
                        <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Reuniones</p>
                        <h2 class="admin-dashboard-section-title">Gestion de clases y estados</h2>
                    </div>
                </div>

                <div class="admin-dashboard-meeting-list-legacy" data-meeting-list>
                    @forelse ($meetings as $meeting)
                        @php
                            $meetingUpdateFormId = 'meeting-update-' . $meeting->id;
                            $meetingDeleteFormId = 'meeting-delete-' . $meeting->id;
                        @endphp
                        <form id="{{ $meetingUpdateFormId }}" action="{{ route('admin.reuniones.update', $meeting) }}" method="POST"
                            class="rounded-[28px] border border-slate-100 bg-white p-5"
                            data-meeting-card
                            data-state="{{ $meeting->estado }}"
                            data-search="{{ \Illuminate\Support\Str::lower(trim($meeting->title . ' ' . $meeting->description . ' ' . ($meeting->docente?->name ?? ''))) }}">
                            @csrf
                            @method('PATCH')

                            <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr_200px_auto] xl:items-center">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <x-status-badge :status="$meeting->estado" />
                                        <span class="text-sm text-slate-500">
                                            {{ optional($meeting->scheduled_at)->format('d M - H:i') }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $meeting->title }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">{{ $meeting->docente?->name }}</p>
                                </div>

                                <p class="text-sm leading-7 text-slate-500">
                                    {{ \Illuminate\Support\Str::limit($meeting->description, 110) }}
                                </p>

                                <select name="estado" class="field-select">
                                    <option value="programada" @selected($meeting->estado === 'programada')>Programada</option>
                                    <option value="en_vivo" @selected($meeting->estado === 'en_vivo')>En vivo</option>
                                    <option value="finalizada" @selected($meeting->estado === 'finalizada')>Finalizada</option>
                                </select>

                                <div class="flex gap-3">
                                    <a href="{{ route('reuniones.show', $meeting) }}" class="btn-secondary">Ver</a>
                                    <button type="submit" class="btn-primary">Actualizar</button>
                                    <button type="submit" form="{{ $meetingDeleteFormId }}" class="btn-danger">Eliminar</button>
                                </div>
                            </div>
                        </form>
                        <form id="{{ $meetingDeleteFormId }}" action="{{ route('admin.reuniones.destroy', $meeting) }}"
                            method="POST" class="hidden"
                            onsubmit="return confirm('Se eliminara esta reunion y sus registros asociados. Continuar?')">
                            @csrf
                            @method('DELETE')
                        </form>
                    @empty
                        <article class="admin-dashboard-empty-card">
                            <strong>Sin reuniones registradas</strong>
                            <p>No hay clases disponibles para administrar todavia.</p>
                        </article>
                    @endforelse
                </div>

                <article class="admin-dashboard-empty-card" data-meeting-empty hidden>
                    <strong>Sin reuniones visibles</strong>
                    <p>No hay reuniones disponibles para mostrar en este momento.</p>
                </article>
            </section>
        </section>

        <section class="admin-dashboard-bottom-grid">
            <section class="surface-panel admin-dashboard-panel" data-dashboard-panel>
                <div class="admin-dashboard-panel-head admin-dashboard-panel-head-tight">
                    <div>
                        <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Usuarios</p>
                        <h2 class="admin-dashboard-section-title">Gestion institucional</h2>
                    </div>

                    <div class="admin-dashboard-panel-meta">
                        <strong data-user-visible-count>{{ $managedUsers->count() }}</strong>
                        <span>cuentas visibles</span>
                    </div>
                </div>

                <div class="admin-dashboard-toolbar admin-dashboard-toolbar-users">
                    <label class="admin-dashboard-search">
                        <span>Buscar</span>
                        <input type="search" placeholder="Nombre, correo o rol" data-user-search>
                    </label>
                </div>

                <div class="admin-dashboard-user-role-strip">
                    @foreach ($roles as $role)
                        @php
                            $roleShare = $metrics['usuarios'] > 0
                                ? round(($role->users_count / $metrics['usuarios']) * 100)
                                : 0;
                        @endphp
                        <article class="admin-dashboard-user-role-pill">
                            <div>
                                <span>{{ $role->name }}</span>
                                <strong>{{ $role->users_count }}</strong>
                            </div>
                            <small>{{ $roleShare }}%</small>
                        </article>
                    @endforeach
                </div>

                <div class="admin-dashboard-user-list-head" aria-hidden="true">
                    <div class="admin-dashboard-user-list-grid">
                        <span>Nombre</span>
                        <span>Correo</span>
                        <span>Rol</span>
                        <span>Estado</span>
                        <span>Acciones</span>
                    </div>
                </div>

                <div class="admin-dashboard-user-list" data-user-list>
                    @foreach ($managedUsers as $managedUser)
                        @php
                            $userDeleteFormId = 'user-delete-' . $managedUser->id;
                            $isAdministratorAccount = $managedUser->role?->slug === 'administrador';
                            $managedUserPhotoUrl = $managedUser->foto_perfil
                                ? route('users.photo', ['user' => $managedUser->id, 'v' => optional($managedUser->updated_at)->timestamp])
                                : null;
                        @endphp
                        <form action="{{ route('admin.users.update', $managedUser) }}" method="POST"
                            class="admin-dashboard-user-card admin-dashboard-user-card-row"
                            data-user-card
                            data-user-form
                            data-search="{{ \Illuminate\Support\Str::lower(trim($managedUser->name . ' ' . $managedUser->email . ' ' . ($managedUser->role?->name ?? ''))) }}">
                            @csrf
                            @method('PATCH')

                            <div class="admin-dashboard-user-row-grid">
                                <div class="admin-dashboard-user-name-cell">
                                    @if ($managedUserPhotoUrl)
                                        <img src="{{ $managedUserPhotoUrl }}" alt="Foto de {{ $managedUser->name }}"
                                            class="admin-dashboard-user-avatar border border-slate-200 object-cover">
                                    @else
                                        <span class="admin-dashboard-user-avatar"
                                            style="background-color: {{ $managedUser->avatar_color ?? '#F57C00' }}">
                                            {{ strtoupper(substr($managedUser->name, 0, 2)) }}
                                        </span>
                                    @endif
                                    <div class="w-full">
                                        <label class="field-label mb-1 lg:hidden">Nombre</label>
                                        <input name="name" type="text" class="field-input" value="{{ $managedUser->name }}"
                                            minlength="3" maxlength="120" autocomplete="name" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="field-label mb-1 lg:hidden">Correo</label>
                                    <input name="email" type="email" class="field-input" value="{{ $managedUser->email }}"
                                        required maxlength="255" autocomplete="email"
                                        pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$">
                                </div>

                                @if ($isAdministratorAccount)
                                    <input type="hidden" name="role_id" value="{{ $managedUser->role_id }}">
                                    <div>
                                        <label class="field-label mb-1 lg:hidden">Rol</label>
                                        <select name="role_id_locked" class="field-select" disabled>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected($managedUser->role_id === $role->id)>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div>
                                        <label class="field-label mb-1 lg:hidden">Rol</label>
                                        <select name="role_id" class="field-select" required>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected($managedUser->role_id === $role->id)>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="field-label mb-1 lg:hidden">Estado</label>
                                    <label class="admin-dashboard-toggle-row admin-dashboard-toggle-box">
                                        @if ($isAdministratorAccount)
                                            <input type="hidden" name="activo" value="{{ $managedUser->activo ? '1' : '0' }}">
                                        @endif
                                        <input type="checkbox" name="activo" value="1"
                                            class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                            {{ $managedUser->activo ? 'checked' : '' }}
                                            @disabled($isAdministratorAccount)>
                                        Activo
                                    </label>
                                </div>

                                <div class="admin-dashboard-user-actions">
                                    @if (! $isAdministratorAccount)
                                        <button type="submit" form="{{ $userDeleteFormId }}" class="btn-danger">Eliminar</button>
                                    @else
                                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Administrador protegido</span>
                                    @endif
                                    <button type="submit" class="btn-secondary hidden" data-user-save-btn>Guardar</button>
                                </div>
                            </div>
                        </form>
                        @unless ($isAdministratorAccount)
                            <form id="{{ $userDeleteFormId }}" action="{{ route('admin.users.destroy', $managedUser) }}"
                                method="POST" class="hidden"
                                onsubmit="return confirm('Se eliminara este usuario y sus datos asociados. Continuar?')">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endunless
                    @endforeach
                </div>

                <article class="admin-dashboard-empty-card" data-user-empty hidden>
                    <strong>Sin usuarios visibles</strong>
                    <p>No hay cuentas que coincidan con la busqueda actual.</p>
                </article>
            </section>
        </section>
    </div>
@endsection
