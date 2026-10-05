<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('roles.editar');
    }

    public function rules(): array
    {
        return [
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'exists:permiso,id_permiso'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
