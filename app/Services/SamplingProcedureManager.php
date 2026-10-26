<?php

namespace App\Services;

use App\Models\SamplingProcedure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SamplingProcedureManager
{
    public function create(array $input, int $company): SamplingProcedure
    {
        $data = Validator::make($input, [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('sampling_procedures')->where('id_empresa', $company)->where('version', $input['version'] ?? null)],
            'version' => ['required', 'string', 'max:30'],
            'producto' => ['nullable', 'string', 'max:150'],
            'requiere_poblacion' => ['nullable', 'boolean'],
            'metodo' => ['required', 'string', 'max:80'],
            'criterio_muestra' => ['required', 'string', 'max:1500'],
            'seleccion_frutos' => ['required', 'string', 'max:1500'],
            'max_frutos' => ['required', 'integer', 'min:1', 'max:'.config('operations.sampling_max_fruits')],
            'tamano_recomendado' => ['nullable', 'integer', 'min:1', 'lte:max_frutos'],
        ])->validate();
        $data = app(MaestroCatalog::class)->validate($data, ['metodo' => 'metodo', 'producto' => 'producto'], $company);
        return SamplingProcedure::create($data + ['id_empresa' => $company, 'activo' => true]);
    }
}
