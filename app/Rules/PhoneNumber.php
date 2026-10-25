<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        if (!preg_match('/^\+?[0-9(][0-9\s().-]*$/', (string) $value) || strlen($digits) < 7 || strlen($digits) > 15) {
            $fail('El teléfono debe contener entre 7 y 15 dígitos. Puedes usar + al inicio, espacios, guiones o paréntesis.');
        }
    }
}
