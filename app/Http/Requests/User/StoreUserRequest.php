<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
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
            'correo' => ['required', 'email', 'max:255', Rule::unique('usuario', 'correo')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^(?=(?:.*\d){7,15}$)\+?[0-9][0-9\s().-]*$/'],
            'cargo' => ['nullable', Rule::in(array_keys(config('agrocloud.cargos', [])))],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
