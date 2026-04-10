<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InstitutionalEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_ends_with(mb_strtolower(trim($value)), '@unifranz.edu.bo')) {
            $fail('Debes usar un correo institucional @unifranz.edu.bo.');
        }
    }
}
