<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\DictamenCalidad;
use App\Models\Inspeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CalidadController extends Controller
{
    public function index(): View
    {
        return view('calidad.index');
    }

    public function show(int $inspeccion): View
    {
        $inspeccion = Inspeccion::query()->forCurrentCompany()->with([
            'muestreo.lote.productor', 'evidencias.validaciones', 'hallazgos', 'dictamen.supervisor', 'inspector',
        ])->findOrFail($inspeccion);
        $revisiones = app(\App\Services\RevisionEvidencias::class)->revisiones($inspeccion);
        $naturalezas = app(\App\Services\RevisionEvidencias::class)->naturalezas($inspeccion);
        $bloqueado = app(\App\Services\IncidenciasOperativas::class)->bloqueaLiberacion($inspeccion->id_empresa, $inspeccion->muestreo->id_lote);
        $revisionCompleta = $inspeccion->evidencias->isNotEmpty() && count($revisiones) === $inspeccion->evidencias->count();
        $distintoInspector = (int) $inspeccion->id_inspector !== (int) auth()->id();
        $evento = $inspeccion->dictamen ? DB::table('eventos_trazabilidad')->where('id_empresa', $inspeccion->id_empresa)->where('entidad', 'DictamenCalidad')->where('registro', $inspeccion->dictamen->id_dictamen)->where('evento', 'REVISION_SUPERVISOR')->latest('id')->first() : null;
        $revisionSupervisor = $evento ? json_decode($evento->cambios, true) : [];
        return view('calidad.show', compact('inspeccion', 'revisiones', 'naturalezas', 'bloqueado', 'revisionCompleta', 'distintoInspector', 'revisionSupervisor'));
    }

    public function documento(int $inspeccion): View
    {
        $inspeccion = Inspeccion::forCurrentCompany()->with(['muestreo.lote.productor', 'inspector', 'dictamen.supervisor'])->findOrFail($inspeccion);
        abort_unless($inspeccion->dictamen, 404, 'La inspección todavía no tiene un dictamen registrado.');
        $evento = DB::table('eventos_trazabilidad')->where('id_empresa', $inspeccion->id_empresa)->where('entidad', 'DictamenCalidad')->where('registro', $inspeccion->dictamen->id_dictamen)->where('evento', 'REVISION_SUPERVISOR')->latest('id')->first();
        $revisionSupervisor = $evento ? json_decode($evento->cambios, true) : [];
        return view('calidad.documento', compact('inspeccion', 'revisionSupervisor'));
    }

    public function store(Request $request, int $inspeccion): RedirectResponse
    {
        abort_unless($request->user()->rol?->codigo === 'SUPERVISOR', 403);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['APROBADO', 'OBSERVADO', 'RECHAZADO'])],
            'motivo' => ['nullable', 'string', 'max:200'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['required', 'string', 'max:3000'],
            'protocolo_calidad' => ['required', 'string', 'max:200'],
            'revision_independiente' => ['accepted'],
        ]);
        $data = app(\App\Services\MaestroCatalog::class)->validate($data, ['categoria' => 'categoria', 'motivo' => 'motivo'], $request->user()->id_empresa);
        if ($data['decision'] === 'APROBADO') $data['motivo'] = null;
        $protocolo = $data['protocolo_calidad'];
        unset($data['protocolo_calidad'], $data['revision_independiente']);
        DB::transaction(function () use ($request, $inspeccion, $data, $protocolo) {
            $inspection = Inspeccion::query()->forCurrentCompany()->lockForUpdate()->findOrFail($inspeccion);
            abort_unless($inspection->estado === 'VALIDADA', 422, 'La inspección no ha sido validada.');
            abort_if($inspection->dictamen()->exists(), 422, 'Esta inspección ya tiene un dictamen.');
            $muestra = $inspection->muestreo;
            $lote = \App\Models\Lote::forCurrentCompany()->lockForUpdate()->findOrFail($muestra->id_lote);
            $inspection->load(['evidencias.validaciones', 'hallazgos']);
            abort_if($data['decision'] === 'APROBADO' && app(\App\Services\IncidenciasOperativas::class)->bloqueaLiberacion($inspection->id_empresa, $lote->id_lote), 422, 'El lote tiene incidencias altas o críticas sin cierre del supervisor. Atiéndelas antes de liberar.');
            if ($data['decision'] !== 'OBSERVADO') {
                abort_unless(app(\App\Services\RevisionEvidencias::class)->completa($inspection), 422, 'Faltan revisiones por imagen. Solicita una reinspección para completar el expediente.');
            }
            $dictamen = DictamenCalidad::create([
                'id_empresa' => $inspection->id_empresa,
                'id_lote' => $lote->id_lote,
                'id_inspeccion' => $inspection->id_inspeccion,
                'id_supervisor' => $request->user()->id_usuario,
                ...$data,
            ]);
            app(\App\Observers\OperationalObserver::class)->record($dictamen, 'REVISION_SUPERVISOR', ['protocolo' => $protocolo, 'revision_independiente' => true, 'fotografias_revisadas' => count(app(\App\Services\RevisionEvidencias::class)->revisiones($inspection))]);
            if ($data['decision'] === 'APROBADO') {
                $muestra->update(['estado' => 'COMPLETADO']);
                $lote->update(['estado' => LoteEstado::CLASIFICADO]);
            } elseif ($data['decision'] === 'RECHAZADO') {
                $muestra->update(['estado' => 'COMPLETADO']);
                $lote->update(['estado' => LoteEstado::RECHAZADO]);
            } else {
                $lote->update(['estado' => LoteEstado::OBSERVADO]);
                // Preserva la inspección validada y abre una nueva inspección para el mismo muestreo.
                Inspeccion::create([
                    'id_empresa' => $inspection->id_empresa,
                    'id_muestreo' => $muestra->id_muestreo,
                    'id_inspector' => null,
                    'solicitada_por' => $request->user()->id_usuario,
                    'estado' => 'EN_CAPTURA',
                ]);
                $muestra->update(['estado' => 'EN_INSPECCION']);
                $lote->update(['estado' => LoteEstado::EN_INSPECCION]);
            }
            // AGROCLOUD_NOTIFICATIONS_INSTALLED
            $decision = $data['decision'];
            $roles = $decision === 'OBSERVADO' ? ['INSPECTOR', 'ADMIN'] : ['PRODUCCION', 'ADMIN'];
            $tipo = $decision === 'OBSERVADO' ? 'REINSPECCION_SOLICITADA' : 'DICTAMEN_'.$decision;
            $titulo = match ($decision) {
                'APROBADO' => 'Lote aprobado para producción',
                'RECHAZADO' => 'Lote rechazado por calidad',
                default => 'Reinspección solicitada',
            };
            $mensaje = 'El lote '.$lote->codigo_lote.' recibió un dictamen '.strtolower($decision).'.';
            $destino = $decision === 'OBSERVADO'
                ? '/muestreos/'.$muestra->id_muestreo
                : '/lotes/'.$lote->id_lote;
            \App\Services\OperationalNotifier::roles(
                (int) $inspection->id_empresa, $roles, $tipo, $titulo,
                $mensaje, $destino,
                $decision === 'APROBADO' ? 'normal' : 'alta', (int) $lote->id_lote
            );
        });
        return redirect()->route('calidad.index')->with('success', 'Dictamen guardado correctamente.');
    }
}
