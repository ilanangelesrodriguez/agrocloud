<?php

namespace App\Http\Requests\Company;

use App\Models\Empresa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    use NormalizesCompanyInput;
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('empresa.editar');
    }

    public function rules(): array
    {
        $routeCompany = $this->route('empresa');
        $empresaId = $routeCompany instanceof Empresa
            ? $routeCompany->getKey()
            : $this->user()?->id_empresa;

        return [
            'ruc' => ['required', 'digits:11', Rule::unique('empresa', 'ruc')->ignore($empresaId, 'id_empresa')],
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'grupo_empresarial' => ['nullable', 'string', 'max:150'],
            'tipo_empresa' => ['nullable', 'string', 'max:80'],
            'rubro' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'ubigeo' => ['nullable', 'digits:6'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30', new \App\Rules\PhoneNumber],
            'correo' => ['nullable', 'email', 'max:255'],
            'sitio_web' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            // Solo EmpresaAdminController utiliza este campo. EmpresaController lo elimina explícitamente.
            'estado' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El teléfono debe contener entre 7 y 15 dígitos y solo puede incluir +, espacios, guiones o paréntesis.',
        ];
    }
}
