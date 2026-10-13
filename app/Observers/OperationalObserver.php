<?php

namespace App\Observers;

use App\Models\EvidenciaInspeccion;
use App\Models\Inspeccion;
use App\Models\Lote;
use App\Models\Muestreo;
use App\Models\Recepcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationalObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'REGISTRADO', $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())->except(['updated_at'])->map(fn ($v, $key) => ['antes' => $model->getRawOriginal($key), 'despues' => $v])->all();
        if ($changes) {
            $this->record($model, 'ACTUALIZADO', $changes);
        }
    }

    public function record(Model $model, string $event, array $changes): void
    {
        if (! Schema::hasTable('eventos_trazabilidad')) {
            return;
        }
        $lot = match (true) {
            $model instanceof Lote => $model->id_lote,
            $model instanceof Muestreo, $model instanceof Recepcion => $model->id_lote,
            $model instanceof Inspeccion => $model->muestreo?->id_lote,
            $model instanceof EvidenciaInspeccion => $model->inspeccion?->muestreo?->id_lote,
            isset($model->id_lote) => $model->id_lote,
            isset($model->id_inspeccion) => Inspeccion::find($model->id_inspeccion)?->muestreo?->id_lote,
            isset($model->id_evidencia) => EvidenciaInspeccion::find($model->id_evidencia)?->inspeccion?->muestreo?->id_lote,
            default => null,
        };
        if (! $lot) {
            return;
        }
        // Store references and operational values only; never copy image/AI payloads.
        $changes = collect($changes)->except(['imagen_url', 'imagen_public_id', 'imagen_detectada_url', 'resultado_ia'])->all();
        DB::table('eventos_trazabilidad')->insert(['id_empresa' => $model->id_empresa, 'id_lote' => $lot, 'id_usuario' => auth()->id(), 'entidad' => class_basename($model), 'registro' => $model->getKey(), 'evento' => $event, 'cambios' => json_encode($changes, JSON_UNESCAPED_UNICODE), 'created_at' => now()]);
    }
}
