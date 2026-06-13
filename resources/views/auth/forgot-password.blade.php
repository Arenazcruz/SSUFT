@extends('layouts.auth', ['title' => 'Recuperar contraseña | UNIFRANZ Stream'])

@section('content')
    <span class="eyebrow">Recuperación</span>
    <h2 class="mt-5 text-3xl font-semibold text-[#111111]">Recuperar contraseña</h2>
    <p class="mt-3 text-sm leading-7 text-slate-500">
        Ingresa tu correo institucional y enviaremos el enlace para restablecer tu acceso.
    </p>

    <form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="field-label">Correo institucional</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$" data-institutional-email
                   class="field-input" placeholder="usuario@unifranz.edu.bo">
        </div>

        <button type="submit" class="btn-primary w-full">Enviar enlace</button>
        <a href="{{ route('login') }}" class="btn-secondary w-full">Volver al inicio de sesión</a>
    </form>
@endsection
