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
        return redirect()->route('recepciones.index');
    }

    public function revisar(Request $request, int $lote)
    {
        $lote = Lote::forCurrentCompany()->with(['productor', 'recepcion.responsable'])->findOrFail($lote);
        return view($request->header('X-Workspace-Drawer') ? 'operaciones._recepcion' : 'operaciones.recepcion', compact('lote'));
    }

    public function recibir(Request $request, int $lote)
    {
        abort_unless($request->user()->rol?->codigo === 'RECEPCION', 403);
        $data = $request->validate(['documento' => 'required|string|max:100', 'transportista' => 'nullable|string|max:150', 'placa' => 'nullable|string|max:20', 'peso_recibido_kg' => 'required|numeric|min:0.01|max:99999999.99', 'recibido_en' => 'required|date|before_or_equal:now', 'observaciones' => 'nullable|string|max:3000']);
        DB::transaction(function () use ($lote, $data, $request) {
            $lot = Lote::forCurrentCompany()->lockForUpdate()->findOrFail($lote);
            abort_if($lot->recepcion()->exists() || $lot->muestreos()->exists() || !in_array($lot->estado->value, ['PENDIENTE_RECEPCION', 'RECIBIDO'], true), 422, 'El lote ya fue recibido o inició su operación.');
            if ((int) round((float) $lot->peso_kg * 100) !== (int) round((float) $data['peso_recibido_kg'] * 100) && blank($data['observaciones'] ?? null)) {
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
        if ($request->header('X-Workspace-Drawer')) return response()->json(['saved' => true, 'module' => 'recepciones', 'message' => 'Recepción confirmada. Lote disponible para muestreo.']);
        return redirect()->route('recepciones.index')->with('success', 'Recepción confirmada. El lote está disponible para muestreo.');
    }

    public function traza(Request $request, int $lote)
    {
        $filtros = $request->validate([
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('desde') ? 'after_or_equal:desde' : null]),
            'evento' => 'nullable|string|max:40', 'responsable' => 'nullable|integer|min:1',
        ]);
        $lote = Lote::forCurrentCompany()->with(['productor', 'recepcion.responsable', 'muestreos.inspector', 'muestreos.inspecciones.inspector', 'muestreos.inspecciones.dictamen.supervisor'])->findOrFail($lote);
        $base = DB::table('eventos_trazabilidad')->where('id_empresa', auth()->user()->id_empresa)->where('id_lote', $lote->id_lote);
        $tipos = (clone $base)->distinct()->orderBy('evento')->pluck('evento');
        $responsables = \App\Models\User::where('id_empresa', auth()->user()->id_empresa)->whereIn('id_usuario', (clone $base)->select('id_usuario'))->orderBy('nombre')->get();
        $eventos = DB::table('eventos_trazabilidad as e')->leftJoin('usuario as u', 'u.id_usuario', '=', 'e.id_usuario')->where('e.id_empresa', auth()->user()->id_empresa)->where('e.id_lote', $lote->id_lote)
            ->when(!empty($filtros['desde']), fn ($q) => $q->whereDate('e.created_at', '>=', $filtros['desde']))
            ->when(!empty($filtros['hasta']), fn ($q) => $q->whereDate('e.created_at', '<=', $filtros['hasta']))
            ->when(!empty($filtros['evento']), fn ($q) => $q->where('e.evento', $filtros['evento']))
            ->when(!empty($filtros['responsable']), fn ($q) => $q->where('e.id_usuario', $filtros['responsable']))
            ->select('e.*', 'u.nombre', 'u.apellido')->orderByDesc('e.id')->paginate(30)->withQueryString();
        $recorrido = collect([['titulo' => 'Lote registrado', 'fecha' => $lote->created_at, 'detalle' => $lote->codigo_lote, 'icono' => 'briefcase']]);
        if ($lote->recepcion) $recorrido->push(['titulo' => 'Recepción física confirmada', 'fecha' => $lote->recepcion->recibido_en, 'detalle' => $lote->recepcion->documento.' · '.$lote->recepcion->responsable?->nombre_completo, 'icono' => 'building']);
        foreach ($lote->muestreos as $muestra) {
            $recorrido->push(['titulo' => 'Muestreo registrado', 'fecha' => $muestra->created_at, 'detalle' => 'Muestreo #'.$muestra->id_muestreo.' · '.$muestra->tamano_muestra.' frutos · '.$muestra->inspector?->nombre_completo, 'icono' => 'clipboard-list']);
            foreach ($muestra->inspecciones as $inspection) {
                $recorrido->push(['titulo' => $inspection->solicitada_por ? 'Reinspección solicitada' : 'Inspección iniciada', 'fecha' => $inspection->created_at, 'detalle' => 'Inspección #'.$inspection->id_inspeccion.' · '.$inspection->inspector?->nombre_completo, 'icono' => 'camera']);
                if ($inspection->validada_en) $recorrido->push(['titulo' => 'Revisión humana finalizada', 'fecha' => $inspection->validada_en, 'detalle' => 'Inspección #'.$inspection->id_inspeccion, 'icono' => 'check']);
                if ($inspection->dictamen) $recorrido->push(['titulo' => 'Dictamen '.mb_strtolower($inspection->dictamen->decision), 'fecha' => $inspection->dictamen->created_at, 'detalle' => 'Inspección #'.$inspection->id_inspeccion.' · '.$inspection->dictamen->supervisor?->nombre_completo, 'icono' => 'shield']);
            }
        }
        $recorrido = $recorrido->filter(fn ($e) => $e['fecha'])->sortBy('fecha')->values();
        return view('operaciones.trazabilidad', compact('lote', 'eventos', 'tipos', 'responsables', 'recorrido', 'filtros'));
    }
}
