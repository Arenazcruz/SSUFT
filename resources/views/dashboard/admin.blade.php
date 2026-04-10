@extends('layouts.app', [
    'title' => 'Dashboard administrador | UNIFRANZ Stream',
    'eyebrow' => 'Panel administrador',
    'pageTitle' => 'Gobierno institucional de la plataforma',
    'pageDescription' => 'Administra usuarios, roles, reuniones y capacidad operativa del ecosistema.',
])

@section('content')
    <div class="grid gap-8">
        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Usuarios</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['usuarios'] }}</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Reuniones</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['reuniones'] }}</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Grabaciones</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['grabaciones'] }}</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">En vivo</p>
                <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $metrics['live'] }}</p>
            </article>
        </section>

        <section class="grid gap-8 xl:grid-cols-[0.82fr_1.18fr]">
            <div class="surface-panel p-7">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Crear usuario</p>
                <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Alta institucional</h2>

                <form action="{{ route('admin.users.store') }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="field-label">Nombre</label>
                        <input id="name" name="name" type="text" class="field-input" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label for="email" class="field-label">Correo institucional</label>
                        <input id="email" name="email" type="email" class="field-input" value="{{ old('email') }}" required pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$">
                    </div>
                    <div>
                        <label for="role_id" class="field-label">Rol</label>
                        <select id="role_id" name="role_id" class="field-select" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="password" class="field-label">Contraseña</label>
                            <input id="password" name="password" type="password" class="field-input" required>
                        </div>
                        <div>
                            <label for="password_confirmation" class="field-label">Confirmar</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="field-input" required>
                        </div>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-500">
                        <input type="checkbox" name="activo" value="1" checked class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200">
                        Activar cuenta al crearla
                    </label>
                    <button type="submit" class="btn-primary w-full">Crear usuario</button>
                </form>
            </div>

            <div class="grid gap-6">
                <div class="surface-panel p-7">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Distribución de roles</p>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        @foreach ($roles as $role)
                            <article class="rounded-[24px] bg-slate-50 p-5">
                                <p class="text-sm font-semibold text-slate-500">{{ $role->name }}</p>
                                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $role->users_count }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="surface-panel p-7">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Usuarios</p>
                            <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Gestión institucional</h2>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @foreach ($users as $managedUser)
                            <form action="{{ route('admin.users.update', $managedUser) }}" method="POST" class="rounded-[28px] border border-slate-100 bg-white p-5">
                                @csrf
                                @method('PATCH')
                                <div class="grid gap-4 xl:grid-cols-[1fr_1fr_220px_180px_auto]">
                                    <input name="name" type="text" class="field-input" value="{{ $managedUser->name }}" required>
                                    <input name="email" type="email" class="field-input" value="{{ $managedUser->email }}" required>
                                    <select name="role_id" class="field-select" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @selected($managedUser->role_id === $role->id)>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-600">
                                        <input type="checkbox" name="activo" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200" {{ $managedUser->activo ? 'checked' : '' }}>
                                        Activo
                                    </label>
                                    <button type="submit" class="btn-secondary">Guardar</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="surface-panel p-7">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Reuniones</p>
                    <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Gestión de clases y estados</h2>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($meetings as $meeting)
                    <form action="{{ route('admin.reuniones.update', $meeting) }}" method="POST" class="rounded-[28px] border border-slate-100 bg-white p-5">
                        @csrf
                        @method('PATCH')
                        <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr_200px_auto] xl:items-center">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <x-status-badge :status="$meeting->estado" />
                                    <span class="text-sm text-slate-500">{{ optional($meeting->scheduled_at)->format('d M · H:i') }}</span>
                                </div>
                                <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $meeting->title }}</h3>
                                <p class="mt-2 text-sm text-slate-500">{{ $meeting->docente?->name }}</p>
                            </div>
                            <p class="text-sm leading-7 text-slate-500">{{ \Illuminate\Support\Str::limit($meeting->description, 110) }}</p>
                            <select name="estado" class="field-select">
                                <option value="programada" @selected($meeting->estado === 'programada')>Programada</option>
                                <option value="en_vivo" @selected($meeting->estado === 'en_vivo')>En vivo</option>
                                <option value="finalizada" @selected($meeting->estado === 'finalizada')>Finalizada</option>
                            </select>
                            <div class="flex gap-3">
                                <a href="{{ route('reuniones.show', $meeting) }}" class="btn-secondary">Ver</a>
                                <button type="submit" class="btn-primary">Actualizar</button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
