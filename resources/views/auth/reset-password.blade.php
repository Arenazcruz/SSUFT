@extends('layouts.auth', ['title' => 'Restablecer contraseña | UNIFRANZ Stream'])

@section('content')
    <span class="eyebrow">Nuevo acceso</span>
    <h2 class="mt-5 text-3xl font-semibold text-[#111111]">Restablecer contraseña</h2>
    <p class="mt-3 text-sm leading-7 text-slate-500">
        Define una nueva contraseña para tu cuenta institucional.
    </p>

    <form action="{{ route('password.store') }}" method="POST" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="field-label">Correo institucional</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email"
                   pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$" data-institutional-email
                   class="field-input" placeholder="usuario@unifranz.edu.bo">
        </div>

        <div>
            <label for="password" class="field-label">Nueva contraseña</label>
            <div class="relative">
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="field-input pr-24" placeholder="Minimo 8 caracteres">
                <button type="button"
                        data-password-toggle-button
                        data-target="password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                    Mostrar
                </button>
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirmar contraseña</label>
            <div class="relative">
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="field-input pr-24" placeholder="Repite tu contraseña">
                <button type="button"
                        data-password-toggle-button
                        data-target="password_confirmation"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                    Mostrar
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Actualizar contraseña</button>
    </form>
@endsection
