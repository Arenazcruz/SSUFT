<?php

namespace App\Http\Requests\Teacher;

use App\Models\Reunion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReunionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Reunion|null $reunion */
        $reunion = $this->route('reunion');

        return ($user?->isRole('docente') ?? false)
            && (! $reunion || $reunion->docente_id === $user->id);
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(['programada', 'en_vivo', 'finalizada', 'cancelada'])],
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
                'programada' => ['programada', 'en_vivo', 'cancelada'],
                'en_vivo' => ['en_vivo', 'finalizada'],
                'finalizada' => ['finalizada'],
                'cancelada' => ['cancelada'],
                default => [],
            };

            if (! in_array($targetState, $allowedTransitions, true)) {
                $validator->errors()->add('estado', 'Transición de estado no permitida para esta clase.');
            }
        });
    }
}
