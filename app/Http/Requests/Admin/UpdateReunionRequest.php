<?php

namespace App\Http\Requests\Admin;

use App\Models\Reunion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReunionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['programada', 'en_vivo', 'finalizada'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Reunion|null $reunion */
            $reunion = $this->route('reunion');
            $targetState = (string) $this->input('estado');

            if (! $reunion) {
                return;
            }

            $allowedTransitions = match ($reunion->estado) {
                'programada' => ['programada', 'en_vivo', 'finalizada'],
                'en_vivo' => ['en_vivo', 'finalizada'],
                'finalizada' => ['finalizada'],
                default => [],
            };

            if (! in_array($targetState, $allowedTransitions, true)) {
                $validator->errors()->add('estado', 'Transición de estado no permitida para esta reunión.');
            }
        });
    }
}
