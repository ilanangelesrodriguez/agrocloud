<?php

namespace App\Services;

use App\Models\Inspeccion;

class ProgresoInspeccion
{
    public function calcular(Inspeccion $inspeccion): array
    {
        $inspeccion->loadMissing(['muestreo', 'evidencias.validaciones', 'hallazgos', 'dictamen']);
        $frutos = $inspeccion->muestreo->frutos()->count();
        $pendientes = $inspeccion->muestreo->frutos()->whereDoesntHave('evidencias', fn ($q) => $q->where('id_inspeccion', $inspeccion->id_inspeccion)->where('pertinencia', '!=', 'INVALIDA'));
        $faltantes = (clone $pendientes)->count();
        $activas = $inspeccion->evidencias->where('pertinencia', '!=', 'INVALIDA');
        $fotos = $activas->count();
        $revisiones = app(RevisionEvidencias::class)->revisiones($inspeccion);
        $validada = $inspeccion->estado === 'VALIDADA';
        $completa = $fotos > 0 && $faltantes === 0 && count($revisiones) === $fotos;
        return [
            'frutos' => $frutos, 'cubiertos' => $frutos - $faltantes, 'faltantes' => $faltantes,
            'pendientes' => $pendientes->orderBy('numero')->limit(12)->get(['numero', 'codigo']),
            'descartadas' => $inspeccion->evidencias->where('pertinencia', 'INVALIDA')->count(),
            'fotos' => $fotos, 'analizadas' => $activas->where('estado_ia', 'COMPLETADO')->count(),
            'revisadas' => count($revisiones), 'lista' => $completa,
            'etapa' => $inspeccion->dictamen ? 0 : ($validada ? 3 : ($fotos > 0 && $faltantes === 0 ? 2 : 1)),
            'completadas' => $inspeccion->dictamen ? 3 : ($validada ? 2 : ($fotos > 0 && $faltantes === 0 ? 1 : 0)),
        ];
    }
}
