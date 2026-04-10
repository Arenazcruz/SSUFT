<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\InstitutionalEmail;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FaceLoginRequest extends FormRequest
{
    private const FACE_DISTANCE_THRESHOLD = 0.48;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new InstitutionalEmail()],
            'remember' => ['nullable', 'boolean'],
            'captured_photo' => ['required', 'string', 'starts_with:data:image/'],
            'face_verified' => ['required', 'accepted'],
            'face_distance' => ['required', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->string('email')->lower()->toString();

        $user = User::query()
            ->where('email', $email)
            ->where('activo', true)
            ->whereNotNull('foto_perfil')
            ->first();

        if (! $user || (float) $this->input('face_distance') > self::FACE_DISTANCE_THRESHOLD) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'No fue posible validar la identidad facial. Intenta de nuevo.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
        $this->session()->regenerate();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos de validación facial. Intenta nuevamente en {$seconds} segundos.",
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip().'|face');
    }
}
