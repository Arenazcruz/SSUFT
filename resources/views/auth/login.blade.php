@extends('layouts.auth', ['title' => 'Iniciar sesión | UNIFRANZ Stream'])

@section('content')
    <span class="eyebrow">Acceso institucional</span>
    <h2 class="mt-5 text-3xl font-semibold text-[#111111]">Iniciar sesión</h2>
    <p class="mt-3 text-sm leading-7 text-slate-500">
        Usa tu correo institucional para ingresar a la plataforma de transmisiones académicas.
    </p>

    <form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="field-label">Correo institucional</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$" data-institutional-email
                   class="field-input" placeholder="usuario@unifranz.edu.bo">
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="field-label mb-0">Contraseña</label>
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-orange-600">¿La olvidaste?</a>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="field-input" placeholder="Tu contraseña">
        </div>

        <label class="flex items-center gap-3 text-sm text-slate-500">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200" {{ old('remember') ? 'checked' : '' }}>
            Recordar sesión en este dispositivo
        </label>

        <button type="submit" class="btn-primary w-full">Entrar a la plataforma</button>
    </form>
@endsection
