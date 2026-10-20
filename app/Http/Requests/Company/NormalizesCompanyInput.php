<?php

namespace App\Http\Requests\Company;

trait NormalizesCompanyInput
{
    protected function companyEmailRules(): array
    {
        return [
            'nullable',
            'email',
            'regex:/^[^@\s]+@(?:[^@\s.]+\.)+[^@\s.]+$/u',
            'max:255',
        ];
    }

    protected function companyWebsiteRules(): array
    {
        return [
            'nullable',
            'url:http,https',
            'regex:/^https?:\/\/(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,}(?::\d{1,5})?(?:[\/?#][^\s]*)?$/i',
            'max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        foreach (['ruc', 'razon_social', 'nombre_comercial', 'grupo_empresarial', 'tipo_empresa', 'rubro', 'direccion', 'telefono', 'correo', 'sitio_web'] as $field) {
            if (is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
                if ($normalized[$field] === '' && !in_array($field, ['ruc', 'razon_social', 'nombre_comercial'], true)) {
                    $normalized[$field] = null;
                }
            }
        }
        $website = $normalized['sitio_web'] ?? null;
        if ($website && !str_contains($website, '://') && !preg_match('~^(javascript|data|vbscript|mailto):~i', $website)) {
            $normalized['sitio_web'] = 'https://'.ltrim($website, '/');
        }
        $this->merge($normalized);
    }
}
