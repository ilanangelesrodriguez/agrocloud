<?php

namespace App\Services;

use App\Models\MaestroValor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaestroCatalog
{
    public function options(string $type, ?int $company = null)
    {
        return MaestroValor::query()->visibleTo($type, $company)->where('estado', true)
            ->when($type === 'variedad', fn ($q) => $q->whereHas('parent', fn ($q) => $q->where('estado', true)))
            ->when($company, fn ($q) => $q->whereNotIn('id', DB::table('maestro_empresa_config')->where('id_empresa', $company)->where('estado', false)->select('maestro_id')))
            ->with('parent')->orderByDesc('id_empresa')->orderBy('nombre')->get()
            ->unique(fn ($item) => mb_strtolower($item->nombre).'|'.$item->parent_id)->values();
    }

    public function validate(array $data, array $fields, ?int $company, array $previous = []): array
    {
        foreach ($fields as $field => $type) {
            if (!array_key_exists($field, $data) || $data[$field] === null || trim((string) $data[$field]) === '') continue;
            if (($previous[$field] ?? null) === $data[$field]
                && ($type !== 'variedad' || ($previous['producto'] ?? null) === ($data['producto'] ?? null))) continue;
            $options = $this->options($type, $company);
            $match = $options->first(fn ($item) => mb_strtolower($item->nombre) === mb_strtolower(trim($data[$field]))
                && ($type !== 'variedad' || mb_strtolower($item->parent?->nombre ?? '') === mb_strtolower($data['producto'] ?? '')));
            if (!$match) throw ValidationException::withMessages([$field => 'Selecciona una opción activa del catálogo'.($type === 'variedad' ? ' correspondiente al producto.' : '.')]);
            $data[$field] = $match->nombre;
        }

        return $data;
    }

    public function isUsed(MaestroValor $item): bool
    {
        foreach (config('maestros.'.$item->tipo.'.fields', []) as $table => $field) {
            $column = DB::connection()->getQueryGrammar()->wrap($field);
            if (DB::table($table)->whereRaw('LOWER('.$column.') = ?', [mb_strtolower($item->nombre)])->when($item->id_empresa, fn ($q) => $q->where('id_empresa', $item->id_empresa))->exists()) return true;
        }
        return false;
    }

    public function pesoKg(float $amount, string $unit): float
    {
        $value = $amount * $this->weightFactor($unit);
        if (($amount > 0 && $value < 0.01) || $value > 99999999.99) {
            throw ValidationException::withMessages(['peso_kg' => 'El peso debe equivaler a entre 0.01 y 99,999,999.99 kg.']);
        }
        return round($value, 2);
    }

    public function weightFactor(string $unit): float
    {
        $code = MaestroValor::query()->where('tipo', 'unidad')->where('nombre', $unit)->value('codigo');
        return match ($code) {
            'kg' => 1, 'g' => 0.001, 't' => 1000,
            default => throw ValidationException::withMessages(['unidad_medida' => 'Para peso selecciona kilogramo, gramo o tonelada.']),
        };
    }
}
