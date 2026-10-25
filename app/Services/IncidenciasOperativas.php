<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** El evento de apertura identifica la incidencia; su seguimiento es inmutable. */
class IncidenciasOperativas
{
    public function consulta(int $empresa, ?int $lote = null)
    {
        return DB::table('eventos_trazabilidad')->where('id_empresa', $empresa)
            ->where('entidad', 'Incidencia')->where('evento', 'INCIDENCIA_ABIERTA')
            ->when($lote, fn ($q) => $q->where('id_lote', $lote));
    }

    public function reconstruir($aperturas): array
    {
        $ids = collect($aperturas)->pluck('id');
        if ($ids->isEmpty()) return [];
        $empresa = collect($aperturas)->first()->id_empresa;
        $seguimientos = DB::table('eventos_trazabilidad')->where('id_empresa', $empresa)
            ->where('entidad', 'Incidencia')->whereIn('registro', $ids)
            ->orderBy('id')->get()->groupBy('registro');
        $result = [];
        foreach ($aperturas as $apertura) {
            $estado = json_decode($apertura->cambios, true) ?? [];
            $historial = collect([$apertura])->merge($seguimientos->get($apertura->id, collect()));
            foreach ($historial as $evento) $estado = array_replace($estado, json_decode($evento->cambios, true) ?? []);
            $result[] = (object) ($estado + ['id' => $apertura->id, 'id_lote' => $apertura->id_lote, 'historial' => $historial]);
        }
        return $result;
    }

    public function bloqueaLiberacion(int $empresa, int $lote): bool
    {
        return collect($this->reconstruir($this->consulta($empresa, $lote)->get()))
            ->contains(fn ($i) => in_array($i->gravedad, ['ALTA', 'CRITICA'], true) && $i->estado !== 'CERRADA');
    }

    public function registrar(int $empresa, int $lote, int $registro, string $evento, array $datos): int
    {
        return DB::table('eventos_trazabilidad')->insertGetId([
            'id_empresa' => $empresa, 'id_lote' => $lote, 'id_usuario' => auth()->id(),
            'entidad' => 'Incidencia', 'registro' => $registro, 'evento' => $evento,
            'cambios' => json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'created_at' => now(),
        ]);
    }
}
