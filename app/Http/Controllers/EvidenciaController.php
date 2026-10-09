<?php

namespace App\Http\Controllers;

use App\Models\EvidenciaInspeccion;
use App\Models\Inspeccion;
use App\Services\AgrocloudAiService;
use App\Services\CloudinaryService;
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
            ->with(['muestreo.lote', 'evidencias.validaciones'])->findOrFail($inspeccion);
        return view('inspecciones.evidencias', compact('inspeccion'));
    }

    public function store(Request $request, int $inspeccion, CloudinaryService $cloudinary): RedirectResponse
    {
        $inspeccion = Inspeccion::query()->forCurrentCompany()->findOrFail($inspeccion);
        InspeccionController::canOperate($inspeccion, $request);
        $request->validate(['foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);

        $upload = null;
        try {
            $upload = $cloudinary->uploadImage($request->file('foto'), 'agrocloud/inspecciones');
            EvidenciaInspeccion::create([
                'id_empresa' => $inspeccion->id_empresa,
                'id_inspeccion' => $inspeccion->id_inspeccion,
                'imagen_url' => $upload['url'],
                'imagen_public_id' => $upload['public_id'],
                'estado_ia' => 'PENDIENTE',
            ]);
        } catch (Throwable $exception) {
            if ($upload) {
                try { $cloudinary->deleteImage($upload['public_id']); } catch (Throwable $cleanupError) { report($cleanupError); }
            }
            report($exception);
            return back()->with('error', 'No se pudo guardar la evidencia. Revisa Cloudinary.');
        }

        return back()->with('success', 'Fotografía guardada correctamente.');
    }

    public function analizar(
        Request $request,
        int $inspeccion,
        int $evidencia,
        AgrocloudAiService $ai
    ): RedirectResponse {
        $inspeccion = Inspeccion::query()->forCurrentCompany()->findOrFail($inspeccion);
        InspeccionController::canOperate($inspeccion, $request);
        $foto = EvidenciaInspeccion::query()->forCurrentCompany()
            ->where('id_inspeccion', $inspeccion->id_inspeccion)->findOrFail($evidencia);
        abort_if($foto->estado_ia === 'PROCESANDO', 409, 'Esta fotografía ya está en proceso.');
        abort_if($foto->estado_ia === 'COMPLETADO', 422, 'Esta fotografía ya fue analizada.');
        $foto->update(['estado_ia' => 'PROCESANDO', 'error_ia' => null]);

        try {
            $data = $ai->detectFromImageUrl($foto->imagen_url);
            $foto->update([
                'estado_ia' => 'COMPLETADO',
                'imagen_detectada_url' => $data['detected_image'],
                'ia_upload_id' => $data['upload_id'],
                'version_modelo' => $data['model_version'] ?? 'v1',
                'resultado_ia' => $data['detection_result'],
                'error_ia' => null,
            ]);
            return back()->with('success', 'Análisis IA completado. Revisa las detecciones.');
        } catch (Throwable $exception) {
            Log::warning('Fallo análisis IA', ['evidencia' => $foto->id_evidencia, 'error' => $exception->getMessage()]);
            $foto->update(['estado_ia' => 'ERROR', 'error_ia' => 'No fue posible analizar la imagen.']);
            return back()->with('error', 'No se pudo analizar la fotografía. Puedes reintentar o registrar hallazgos manuales.');
        }
    }
}
