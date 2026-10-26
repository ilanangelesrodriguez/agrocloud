<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserEmail implements ValidationRule
{
    public function __construct(private ?int $ignore = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::where(function ($query) use ($value) {
            $email = mb_strtolower(trim((string) $value));
            $query->whereRaw('LOWER(TRIM(correo)) = ?', [$email])->orWhereRaw('LOWER(TRIM(pending_email)) = ?', [$email]);
        })
            ->when($this->ignore !== null, fn ($q) => $q->where('id_usuario', '!=', $this->ignore))->exists()) {
            $fail('Este correo ya pertenece a una cuenta.');
        }
    }
}
