<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('permisos.crear');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/', 'unique:permiso,codigo'],
            'nombre' => ['required', 'string', 'max:150'],
            'modulo' => ['required', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
