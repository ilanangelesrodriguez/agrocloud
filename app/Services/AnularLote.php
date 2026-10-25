<?php

namespace App\Services;

use App\Enums\LoteEstado;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnularLote
{
    public function ejecutar(int $id): void
    {
        abort_unless(auth()->user()?->hasPermission('lotes.eliminar'), 403);
        DB::transaction(function () use ($id) {
            $lote = Lote::forCurrentCompany()->lockForUpdate()->findOrFail($id);
            if ($lote->estado === LoteEstado::ANULADO) return;
            if ($lote->estado !== LoteEstado::PENDIENTE_RECEPCION || $lote->recepcion()->exists()
                || $lote->muestreos()->exists()
                || DB::table('dictamen_calidad')->where('id_lote', $id)->exists()) {
                throw ValidationException::withMessages(['lote' => 'Solo se pueden anular lotes pendientes de recepción. El lote ya tiene actividad operativa y debe conservar su expediente.']);
            }
            $lote->update(['estado' => LoteEstado::ANULADO]);
            app(\App\Observers\OperationalObserver::class)->record($lote, 'ANULADO', ['motivo' => 'Anulación antes de la recepción física. Se conserva el historial.']);
        });
    }
}
