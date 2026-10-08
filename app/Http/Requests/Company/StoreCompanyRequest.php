<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'ruc' => ['required', 'digits:11', Rule::unique('empresa', 'ruc')],
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
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^(?=(?:.*\d){7,15}$)\+?[0-9][0-9\s().-]*$/'],
            'correo' => ['nullable', 'email', 'max:255'],
            'sitio_web' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
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
