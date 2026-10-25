<?php

namespace App\Http\Requests\Concerns;

trait NormalizesEmail
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('correo'))) {
            $this->merge(['correo' => mb_strtolower(trim($this->input('correo')))]);
        }
    }
}
