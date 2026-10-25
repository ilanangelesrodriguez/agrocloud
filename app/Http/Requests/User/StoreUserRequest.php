<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use \App\Http\Requests\Concerns\NormalizesEmail;

    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('usuarios.crear');
    }

    public function rules(): array
    {
        return [
            'id_rol' => [
                'required',
                Rule::exists('rol', 'id_rol')->where(fn ($query) => $query
                    ->where('estado', true)
                    ->where('codigo', '!=', 'SUPERADMIN')),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'apellido' => ['required', 'string', 'max:150'],
            'correo' => [
                'required',
                'email',
                'regex:/^[^@\s]+@(?:[^@\s.]+\.)+[^@\s.]+$/u',
                'max:255',
                new \App\Rules\UniqueUserEmail,
            ],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'telefono' => ['nullable', 'string', 'max:30', new \App\Rules\PhoneNumber],
            'cargo' => ['nullable', 'string', 'max:150'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estado' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El teléfono debe contener entre 7 y 15 dígitos y solo puede incluir +, espacios, guiones o paréntesis.',
            'correo.regex' => 'Ingresa un correo válido con un dominio completo, por ejemplo usuario@empresa.com.',
        ];
    }
}
