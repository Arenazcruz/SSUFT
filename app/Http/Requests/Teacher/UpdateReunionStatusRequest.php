<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReunionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('docente') ?? false;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['programada', 'en_vivo', 'finalizada'])],
        ];
    }
}
