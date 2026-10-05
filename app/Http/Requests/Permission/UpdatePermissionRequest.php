<?php

namespace App\Http\Requests\Permission;

use App\Models\Permiso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('permisos.editar');
    }

    public function rules(): array
    {
        /** @var Permiso|null $permiso */
        $permiso = $this->route('permiso');

        return [
            'codigo' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('permiso', 'codigo')->ignore($permiso?->getKey(), 'id_permiso'),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'modulo' => ['required', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['nullable', 'boolean'],
        ];
    }
}
