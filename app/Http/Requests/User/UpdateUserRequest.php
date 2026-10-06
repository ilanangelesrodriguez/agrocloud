<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('usuarios.editar');
    }

    public function rules(): array
    {
        /** @var User|null $usuario */
        $usuario = $this->route('usuario');

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
                'max:255',
                Rule::unique('usuario', 'correo')->ignore($usuario?->getKey(), 'id_usuario'),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'cargo' => ['nullable', Rule::in(array_keys(config('agrocloud.cargos', [])))],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
