<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StoreReunionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('docente') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['nullable', 'string', 'max:1500'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $description = Str::of((string) $this->input('description'))->trim()->toString();

        $this->merge([
            'title' => trim((string) $this->input('title')),
            'description' => $description === '' ? null : $description,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $scheduledAt = $this->date('scheduled_at');

            if (! $scheduledAt) {
                return;
            }

            if ($scheduledAt->isPast()) {
                $validator->errors()->add('scheduled_at', 'La fecha y hora debe ser igual o posterior al momento actual.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'duration_minutes.min' => 'La duración mínima permitida es de 30 minutos.',
            'duration_minutes.max' => 'La duración máxima permitida es de 240 minutos.',
        ];
    }
}
