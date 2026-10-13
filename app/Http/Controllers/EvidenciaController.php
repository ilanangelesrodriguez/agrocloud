<?php

namespace App\Http\Controllers;

use App\Models\EvidenciaInspeccion;
use App\Models\Inspeccion;
use App\Services\AgrocloudAiService;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class EvidenciaController extends Controller
{
    public function index(int $inspeccion): View
    {
        $inspeccion = Inspeccion::query()->forCurrentCompany()
            ->with(['muestreo.lote', 'evidencias.validaciones'])
            ->findOrFail($inspeccion);

        return view('inspecciones.evidencias', compact('inspeccion'));
    }

    public function store(Request $request, int $inspeccion, CloudinaryService $cloudinary): RedirectResponse|JsonResponse
    {
        $inspeccion = Inspeccion::query()->forCurrentCompany()->findOrFail($inspeccion);
        InspeccionController::canOperate($inspeccion, $request);

        if ($request->filled('numero_fruto')) {
            $request->validate(['numero_fruto' => 'integer|min:1|max:100000']);
            $fruto = $inspeccion->muestreo->frutos()->where('numero', $request->integer('numero_fruto'))->first();
            if (!$fruto) throw \Illuminate\Validation\ValidationException::withMessages(['numero_fruto' => 'El fruto no pertenece a este muestreo.']);
            $request->merge(['id_fruto' => $fruto->id]);
        }

        // Menos de 4 MB, considerando también el cuerpo de la petición a FastAPI/Vercel.
        // La interfaz comprime a ~2,5 MiB y el backend impone 3 MiB aun sin JavaScript.
        $request->validate([
            'id_fruto' => [$inspeccion->muestreo->frutos()->exists() ? 'required' : 'nullable', 'integer', \Illuminate\Validation\Rule::exists('frutos_muestra', 'id')->where('id_empresa', $inspeccion->id_empresa)->where('id_muestreo', $inspeccion->id_muestreo)],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $upload = null;
        try {
            $upload = $cloudinary->uploadImage($request->file('foto'), 'agrocloud/inspecciones');
            $foto = EvidenciaInspeccion::create([
                'id_empresa' => $inspeccion->id_empresa,
                'id_inspeccion' => $inspeccion->id_inspeccion,
                'id_fruto' => $request->input('id_fruto'),
                'imagen_url' => $upload['url'],
                'imagen_public_id' => $upload['public_id'],
                'estado_ia' => 'PENDIENTE',
            ]);
        } catch (Throwable $exception) {
            if ($upload) {
                try {
                    $cloudinary->deleteImage($upload['public_id']);
                } catch (Throwable $cleanupError) {
                    report($cleanupError);
                }
            }
            report($exception);
            return $this->error($request, 'No se pudo guardar la fotografía. Revisa el servicio de almacenamiento.', 502);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Fotografía guardada. Ya puedes analizarla con IA.',
                'evidencia_id' => $foto->id_evidencia,
                'evidencias_count' => $inspeccion->evidencias()->count(),
                'html' => $this->renderCard($foto, $inspeccion),
            ], 201);
        }

        return back()->with('success', 'Fotografía guardada correctamente.');
    }

    public function analizar(
        Request $request,
        int $inspeccion,
        int $evidencia,
        AgrocloudAiService $ai
    ): RedirectResponse|JsonResponse {
        $inspeccion = Inspeccion::query()->forCurrentCompany()->findOrFail($inspeccion);
        InspeccionController::canOperate($inspeccion, $request);

        $foto = EvidenciaInspeccion::query()->forCurrentCompany()
            ->where('id_inspeccion', $inspeccion->id_inspeccion)
            ->findOrFail($evidencia);

        abort_if($foto->estado_ia === 'COMPLETADO', 422, 'Esta fotografía ya fue analizada.');

        // Cambio atómico: bloquea análisis simultáneos de la misma fotografía.
        // También permite recuperar un trabajo PROCESANDO atascado más de 4 minutos.
        $claimed = EvidenciaInspeccion::query()->forCurrentCompany()
            ->where('id_evidencia', $foto->id_evidencia)
            ->where(function ($query) {
                $query->whereIn('estado_ia', ['PENDIENTE', 'ERROR'])
                    ->orWhere(function ($stale) {
                        $stale->where('estado_ia', 'PROCESANDO')
                            ->where('updated_at', '<', now()->subMinutes(4));
                    });
            })
            ->update(['estado_ia' => 'PROCESANDO', 'error_ia' => null]);

        abort_if($claimed !== 1, 409, 'Esta fotografía ya se está analizando. Espera a que termine.');
        app(\App\Observers\OperationalObserver::class)->record($foto, 'ANALISIS_SOLICITADO', ['estado_ia' => ['antes' => $foto->estado_ia, 'despues' => 'PROCESANDO']]);
        $foto->refresh();

        try {
            // Este método utiliza exclusivamente POST /api/v1/detect (multipart) en AgroCloud AI.
            $data = $ai->detectFromImageUrl($foto->imagen_url);
            $foto->update([
                'estado_ia' => 'COMPLETADO',
                'imagen_detectada_url' => $data['detected_image'],
                'ia_upload_id' => $data['upload_id'],
                'version_modelo' => $data['model_version'] ?? 'v1',
                'resultado_ia' => $data['detection_result'],
                'error_ia' => null,
            ]);
            $message = 'Análisis IA completado. Confirma o descarta las detecciones en Validación.';

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => $message,
                    'evidencia_id' => $foto->id_evidencia,
                    'html' => $this->renderCard($foto, $inspeccion),
                ]);
            }

            return back()->with('success', $message);
        } catch (Throwable $exception) {
            Log::warning('Fallo análisis IA', [
                'evidencia' => $foto->id_evidencia,
                'error' => $exception->getMessage(),
            ]);
            $foto->update([
                'estado_ia' => 'ERROR',
                'error_ia' => 'No fue posible completar el análisis. Reintenta o registra hallazgos manuales.',
            ]);
            // AGROCLOUD_NOTIFICATIONS_INSTALLED
            \App\Services\OperationalNotifier::user(
                (int) $inspeccion->id_empresa, (int) $inspeccion->id_inspector,
                'IA_ERROR', 'Error en análisis de imagen',
                'No se pudo analizar la evidencia #'.$foto->id_evidencia.'. Reintenta el análisis.',
                '/inspecciones/'.$inspeccion->id_inspeccion.'/evidencias',
                'alta', (int) $inspeccion->muestreo->id_lote
            );
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El servicio IA no pudo analizar la imagen. Puedes reintentar.',
                    'detail' => $exception->getMessage(),
                    'evidencia_id' => $foto->id_evidencia,
                    'html' => $this->renderCard($foto, $inspeccion),
                ], 502);
            }
            return back()->with('error', 'El servicio IA no pudo analizar la fotografía. Puedes reintentar o registrar hallazgos manuales.');
        }
    }

    private function renderCard(EvidenciaInspeccion $foto, Inspeccion $inspeccion): string
    {
        $foto->load('validaciones');
        return view('inspecciones._evidencia', compact('foto', 'inspeccion'))->render();
    }

    private function error(Request $request, string $message, int $status): RedirectResponse|JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['ok' => false, 'message' => $message], $status)
            : back()->with('error', $message);
    }
}
