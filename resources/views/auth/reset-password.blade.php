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
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="field-input" placeholder="Minimo 8 caracteres">
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirmar contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="field-input" placeholder="Repite tu contraseña">
        </div>

        <button type="submit" class="btn-primary w-full">Actualizar contraseña</button>
    </form>
@endsection
