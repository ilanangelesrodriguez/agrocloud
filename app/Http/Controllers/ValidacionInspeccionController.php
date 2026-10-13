<?php

namespace App\Http\Controllers;

use App\Enums\LoteEstado;
use App\Models\HallazgoManual;
use App\Models\Inspeccion;
use App\Models\ValidacionDeteccion;
use App\Services\MaestroCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ValidacionInspeccionController extends Controller
{
    public function index(int $inspeccion): View
    {
        $inspeccion = $this->find($inspeccion);

        return view('inspecciones.validacion', compact('inspeccion'));
    }

    public function validarDeteccion(Request $request, int $inspeccion, int $evidencia): RedirectResponse|JsonResponse
    {
        $inspeccion = $this->find($inspeccion);
        InspeccionController::canOperate($inspeccion, $request);
        $foto = $inspeccion->evidencias->firstWhere('id_evidencia', $evidencia);
        abort_unless($foto && $foto->estado_ia === 'COMPLETADO', 404);

        $detections = $foto->resultado_ia['detections'] ?? [];
        $data = $request->validate([
            'indice_deteccion' => ['required', 'integer', 'min:0'],
            'confirmada' => ['required', Rule::in(['0', '1'])],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
        abort_unless(isset($detections[$data['indice_deteccion']]), 422, 'Detección no encontrada.');

        $validacion = ValidacionDeteccion::updateOrCreate(
            ['id_evidencia' => $foto->id_evidencia, 'indice_deteccion' => $data['indice_deteccion']],
            [
                'id_empresa' => $foto->id_empresa,
                'id_inspector' => $request->user()->id_usuario,
                'confirmada' => $data['confirmada'] === '1',
                'observaciones' => $data['observaciones'] ?? null,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Validación guardada.',
                'confirmada' => $validacion->confirmada,
                'evidencia_id' => $foto->id_evidencia,
                'indice_deteccion' => (int) $data['indice_deteccion'],
            ]);
        }

        return back()->with('success', 'Validación guardada.');
    }

    public function hallazgo(Request $request, int $inspeccion): RedirectResponse|JsonResponse
    {
        $inspeccion = $this->find($inspeccion);
        InspeccionController::canOperate($inspeccion, $request);
        $data = $request->validate([
            'clase' => ['required', 'string', 'max:150'],
            'observaciones' => ['required', 'string', 'max:2000'],
            'id_evidencia' => ['nullable', 'integer'],
        ]);
        $evidenciaId = $data['id_evidencia'] ?? null;
        abort_if($evidenciaId && ! $inspeccion->evidencias->contains('id_evidencia', (int) $evidenciaId), 422,
            'La evidencia no pertenece a esta inspección.');

        $data = app(MaestroCatalog::class)->validate($data, ['clase' => 'defecto'], $inspeccion->id_empresa);
        $hallazgo = HallazgoManual::create([
            'id_empresa' => $inspeccion->id_empresa,
            'id_inspeccion' => $inspeccion->id_inspeccion,
            'id_evidencia' => $evidenciaId,
            'id_inspector' => $request->user()->id_usuario,
            'clase' => $data['clase'],
            'observaciones' => $data['observaciones'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Hallazgo manual registrado.',
                'hallazgo' => [
                    'id' => $hallazgo->id_hallazgo,
                    'clase' => $hallazgo->clase,
                    'observaciones' => $hallazgo->observaciones,
                    'evidencia_id' => $hallazgo->id_evidencia,
                ],
            ], 201);
        }

        return back()->with('success', 'Hallazgo manual registrado.');
    }

    public function finalizar(Request $request, int $inspeccion): RedirectResponse
    {
        return DB::transaction(function () use ($request, $inspeccion) {
            $inspeccion = Inspeccion::query()->forCurrentCompany()->lockForUpdate()->with(['muestreo.lote', 'evidencias.validaciones', 'hallazgos', 'dictamen'])->findOrFail($inspeccion);
            InspeccionController::canOperate($inspeccion, $request);
            $data = $request->validate(['observaciones' => ['nullable', 'string', 'max:3000']]);
            abort_if($inspeccion->evidencias->isEmpty(), 422, 'Registra al menos una fotografía.');
            $sinEvidencia = $inspeccion->muestreo->frutos()->whereDoesntHave('evidencias', fn ($q) => $q->where('id_inspeccion', $inspeccion->id_inspeccion))->count();
            abort_if($sinEvidencia > 0, 422, "Faltan fotografías de {$sinEvidencia} frutos de la muestra. Registra cada fruto antes de finalizar.");

            foreach ($inspeccion->evidencias as $foto) {
                $detecciones = $foto->estado_ia === 'COMPLETADO' ? ($foto->resultado_ia['detections'] ?? []) : [];
                if ($foto->estado_ia !== 'COMPLETADO' && $inspeccion->hallazgos->isEmpty()) {
                    return back()->with('error', 'Analiza las fotografías o documenta un hallazgo manual si la IA no está disponible.');
                }
                if (count($detecciones) !== $foto->validaciones->count()) {
                    return back()->with('error', 'Faltan detecciones IA por confirmar o descartar.');
                }
            }

            DB::transaction(function () use ($inspeccion, $data) {
                $inspeccion->update([
                    'estado' => 'VALIDADA',
                    'observaciones' => $data['observaciones'] ?? null,
                    'validada_en' => now(),
                ]);
                $muestra = $inspeccion->muestreo;
                $muestra->update(['estado' => 'PENDIENTE_DICTAMEN']);
                // Se mantiene el valor de base de datos por compatibilidad; la interfaz
                // lo comunica claramente como pendiente de dictamen del supervisor.
                $muestra->lote->update(['estado' => LoteEstado::PENDIENTE_VALIDACION]);
                // AGROCLOUD_NOTIFICATIONS_INSTALLED
                \App\Services\OperationalNotifier::roles(
                    (int) $inspeccion->id_empresa, ['SUPERVISOR'], 'INSPECCION_VALIDADA',
                    'Inspección pendiente de dictamen',
                    'La inspección #'.$inspeccion->id_inspeccion.' del lote '.$muestra->lote->codigo_lote.' está lista para revisión.',
                    '/calidad/inspecciones/'.$inspeccion->id_inspeccion,
                    'alta', (int) $muestra->lote->id_lote
                );
            });

            return redirect()->route('inspecciones.show', $inspeccion->id_inspeccion)
                ->with('success', 'Inspección validada. Ahora está pendiente del dictamen del supervisor.');
        });
    }

    private function find(int $id): Inspeccion
    {
        return Inspeccion::query()->forCurrentCompany()->with([
            'muestreo.lote', 'evidencias.validaciones', 'hallazgos', 'dictamen',
        ])->findOrFail($id);
    }
}
