<?php

namespace App\Http\Requests\Profile;

use App\Rules\InstitutionalEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email,'.$userId, new InstitutionalEmail()],
            'avatar_color' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'foto_perfil' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'captured_photo' => ['nullable', 'string', 'starts_with:data:image/'],
            'remove_photo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }
}
