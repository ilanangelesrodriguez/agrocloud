<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El formulario público ya guarda el correo en minúsculas.
     * Se aplica la misma normalización al login y a su rate limiter.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('correo'))) {
            $this->merge(['correo' => mb_strtolower(trim($this->input('correo')))]);
        }
    }

    public function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            fn ($query) => $query->whereRaw('LOWER(TRIM(correo)) = ?', [$this->string('correo')->toString()])->whereNull('invitation_hash'),
            'password' => $this->string('password')->toString(),
            'estado' => true,
        ];

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'correo' => 'No se pudo iniciar sesión. Comprueba el correo y contraseña; si recibiste una invitación, activa primero la cuenta desde su enlace.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        throw ValidationException::withMessages([
            'correo' => "Demasiados intentos. Vuelve a intentarlo en {$seconds} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('correo')->toString()).'|'.$this->ip());
    }
}
