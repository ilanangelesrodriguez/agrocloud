<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class UbigeoService
{
    public function departamentos(): \Illuminate\Support\Collection
    {
        return collect(json_decode(file_get_contents(public_path('data/ubigeo/1_ubigeo_departamentos.json')), true)['ubigeo_departamentos'])->sortBy('departamento')->values();
    }

    public function provincias(string $departamento): \Illuminate\Support\Collection
    {
        $parent = $this->departamentos()->first(fn ($row) => $row['departamento'] === $departamento);
        if (!$parent) return collect();
        return collect(json_decode(file_get_contents(public_path('data/ubigeo/2_ubigeo_provincias.json')), true)['ubigeo_provincias'])->where('departamento_id', $parent['id'])->sortBy('provincia')->values();
    }

    public function normalize(array $data): array
    {
        $parent = null;
        foreach (['departamento', 'provincia', 'distrito'] as $level) {
            $value = trim((string) ($data[$level] ?? ''));
            if ($value === '') {
                $data[$level] = null;
                $parent = null;
                continue;
            }
            $file = ['departamento' => '1_ubigeo_departamentos', 'provincia' => '2_ubigeo_provincias', 'distrito' => '3_ubigeo_distritos'][$level];
            $key = ['departamento' => 'departamentos', 'provincia' => 'provincias', 'distrito' => 'distritos'][$level];
            $items = json_decode(file_get_contents(public_path('data/ubigeo/'.$file.'.json')), true)['ubigeo_'.$key];
            $parentKey = ['provincia' => 'departamento_id', 'distrito' => 'provincia_id'][$level] ?? null;
            $item = collect($items)->first(fn ($row) => mb_strtoupper($row[$level]) === mb_strtoupper($value)
                && (!$parentKey || ($parent && (int) $row[$parentKey] === (int) $parent['id'])));
            if (!$item) {
                throw ValidationException::withMessages([$level => 'Selecciona una ubicación válida de la lista y sus campos anteriores.']);
            }
            $data[$level] = $item[$level];
            $parent = $item;
        }
        $data['ubigeo'] = $parent && !empty($data['distrito']) ? str_pad((string) $parent['ubigeo'], 6, '0', STR_PAD_LEFT) : null;

        return $data;
    }
}
