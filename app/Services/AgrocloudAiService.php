<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Adaptador para el microservicio FastAPI. La URL es pública; la clave se
 * configura exclusivamente mediante AGROCLOUD_AI_API_KEY.
 */
class AgrocloudAiService
{
    private const BASE_URL = 'https://agrocloud-ai-seven.vercel.app';
    private const MODEL_VERSION = 'v1';

    public function detectFromImageUrl(string $url): array
    {
        $key = trim((string) config('services.agrocloud_ai.api_key', ''));
        if ($key === '') {
            throw new RuntimeException('Falta AGROCLOUD_AI_API_KEY en el archivo .env o en las variables del entorno.');
        }

        $image = Http::timeout(30)->get($url);
        if ($image->failed()) {
            throw new RuntimeException('No se pudo descargar la imagen almacenada para enviarla a la API de IA.');
        }

        $body = $image->body();
        $maxBytes = 4 * 1024 * 1024;
        if (strlen($body) > $maxBytes) {
            throw new RuntimeException('La imagen supera el límite de 4 MB para el endpoint directo de la API de IA.');
        }

        $contentType = (string) $image->header('Content-Type', 'image/jpeg');
        if (! str_starts_with(strtolower($contentType), 'image/')) {
            throw new RuntimeException('La URL de la evidencia no devolvió una imagen válida.');
        }

        $response = Http::timeout(110)
            ->acceptJson()
            ->withHeaders(['X-API-Key' => $key])
            ->attach('file', $body, 'evidencia.jpg', $contentType)
            ->post(self::BASE_URL.'/api/v1/detect?model_version='.self::MODEL_VERSION);

        if ($response->failed()) {
            throw new RuntimeException('La API de IA respondió con error HTTP '.$response->status().'.');
        }

        $data = $response->json();
        if (! is_array($data)
            || ! isset($data['upload_id'], $data['detected_image'])
            || ! is_array($data['detection_result'] ?? null)
            || ! is_array($data['detection_result']['detections'] ?? null)) {
            throw new RuntimeException('La API de IA devolvió una respuesta incompleta.');
        }

        return $data;
    }
}
