<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

final class PasswordPolicy
{
    public static function rules(): array
    {
        return ['string', 'max:255', Password::min(12)->mixedCase()->numbers()];
    }
}
