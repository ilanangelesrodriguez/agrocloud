<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Recepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperacionController extends Controller
{
    public function recepciones()
    {
        $pendientes = Lote::forCurrentCompany()->whereIn('estado', ['PENDIENTE_RECEPCION', 'RECIBIDO'])->whereDoesntHave('recepcion')->with('productor')->latest('id_lote')->paginate(15);
        $recibidas = Recepcion::forCurrentCompany()->with(['lote', 'responsable'])->latest('recibido_en')->limit(20)->get();

        return view('operaciones.recepciones', compact('pendientes', 'recibidas'));
    }

    public function recibir(Request $request, int $lote)
    {
        abort_unless($request->user()->rol?->codigo === 'RECEPCION', 403);
        $data = $request->validate(['documento' => 'required|string|max:100', 'transportista' => 'nullable|string|max:150', 'placa' => 'nullable|string|max:20', 'peso_recibido_kg' => 'required|numeric|min:0.01|max:99999999.99', 'recibido_en' => 'required|date|before_or_equal:now', 'observaciones' => 'nullable|string|max:3000']);
        DB::transaction(function () use ($lote, $data, $request) {
            $lot = Lote::forCurrentCompany()->lockForUpdate()->findOrFail($lote);
            abort_if($lot->recepcion()->exists() || $lot->muestreos()->exists() || !in_array($lot->estado->value, ['PENDIENTE_RECEPCION', 'RECIBIDO'], true), 422, 'El lote ya fue recibido o inició su operación.');
            if (abs((float) $lot->peso_kg - (float) $data['peso_recibido_kg']) >= 0.01 && blank($data['observaciones'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['observaciones' => 'Explica la diferencia entre el peso declarado y el recibido en las condiciones de ingreso.']);
            }
            Recepcion::create($data + ['id_empresa' => $lot->id_empresa, 'id_lote' => $lot->id_lote, 'id_usuario' => $request->user()->id_usuario]);
            $lot->update(['estado' => \App\Enums\LoteEstado::RECIBIDO, 'fecha_recepcion' => $data['recibido_en']]);
        });

        // AGROCLOUD_NOTIFICATIONS_INSTALLED
        $loteConfirmado = Lote::forCurrentCompany()->findOrFail($lote);
        \App\Services\OperationalNotifier::roles(
            (int) $loteConfirmado->id_empresa, ['INSPECTOR'], 'LOTE_RECIBIDO',
            'Lote disponible para muestreo',
            'Se confirmó la recepción del lote '.$loteConfirmado->codigo_lote.'. Ya puedes crear su muestreo.',
            '/muestreos/crear?lote='.$loteConfirmado->id_lote, 'normal', (int) $loteConfirmado->id_lote
        );
        return redirect()->route('recepciones.index')->with('success', 'Recepción confirmada. El lote está disponible para muestreo.');
    }

    public function traza(int $lote)
    {
        $lote = Lote::forCurrentCompany()->with(['productor', 'recepcion.responsable', 'muestreos.inspecciones.dictamen'])->findOrFail($lote);
        $eventos = DB::table('eventos_trazabilidad as e')->leftJoin('usuario as u', 'u.id_usuario', '=', 'e.id_usuario')->where('e.id_empresa', auth()->user()->id_empresa)->where('e.id_lote', $lote->id_lote)->select('e.*', 'u.nombre', 'u.apellido')->orderByDesc('e.id')->paginate(30);

        return view('operaciones.trazabilidad', compact('lote', 'eventos'));
    }
}
