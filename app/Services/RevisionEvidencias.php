<?php

namespace App\Services;

use App\Models\EvidenciaInspeccion;
use App\Models\Inspeccion;
use Illuminate\Support\Facades\DB;

/** Revisiones explícitas y auditables, sin alterar las predicciones del servicio IA. */
class RevisionEvidencias
{
    public function huella(EvidenciaInspeccion $foto, Inspeccion $inspeccion): string
    {
        return hash('sha256', json_encode([
            $foto->estado_ia, $foto->resultado_ia, $foto->version_modelo,
            $foto->validaciones->sortBy('indice_deteccion')->map(fn ($v) => [$v->indice_deteccion, $v->confirmada, $v->observaciones])->values()->all(),
            $inspeccion->hallazgos->where('id_evidencia', $foto->id_evidencia)->sortBy('id_hallazgo')->map(fn ($h) => [$h->id_hallazgo, $h->clase, $h->observaciones])->values()->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function revisiones(Inspeccion $inspeccion, ?\Illuminate\Support\Collection $eventos = null): array
    {
        $eventos ??= DB::table('eventos_trazabilidad')
            ->where('id_empresa', $inspeccion->id_empresa)
            ->where('entidad', 'EvidenciaInspeccion')->where('evento', 'REVISION_IMAGEN')
            ->whereIn('registro', $inspeccion->evidencias->modelKeys())
            ->orderByDesc('id')->get()->unique('registro');
        $result = [];
        foreach ($eventos as $evento) {
            $datos = json_decode($evento->cambios, true) ?? [];
            $foto = $inspeccion->evidencias->firstWhere('id_evidencia', $evento->registro);
            if ($foto && hash_equals($this->huella($foto, $inspeccion), (string) ($datos['huella'] ?? ''))) {
                $result[$evento->registro] = $datos + ['responsable' => $evento->id_usuario, 'fecha' => $evento->created_at];
            }
        }
        return $result;
    }

    public function naturalezas(Inspeccion $inspeccion): array
    {
        return DB::table('eventos_trazabilidad')->where('id_empresa', $inspeccion->id_empresa)
            ->where('entidad', 'HallazgoManual')->where('evento', 'HALLAZGO_CATEGORIZADO')
            ->whereIn('registro', $inspeccion->hallazgos->modelKeys())->get()
            ->mapWithKeys(fn ($e) => [$e->registro => json_decode($e->cambios, true)['naturaleza'] ?? 'OTRO'])->all();
    }

    public function completa(Inspeccion $inspeccion): bool
    {
        return $inspeccion->evidencias->isNotEmpty()
            && count($this->revisiones($inspeccion)) === $inspeccion->evidencias->count();
    }
}
