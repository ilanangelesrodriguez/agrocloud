<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integración privada Laravel -> FastAPI.
 * Siempre utiliza POST /api/v1/detect (multipart); ninguna API key viaja al navegador.
 */
class AgrocloudAiService
{
    private const BASE_URL = 'https://agrocloud-ai-seven.vercel.app';
    private const MODEL_VERSION = 'v1';

    /**
     * @return array{available: bool, status: int|null, message: string|null, docs: string|null, health: string|null}
     */
    public function status(): array
    {
        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => config('services.agrocloud_ai.ca_bundle') ?: true])
                ->acceptJson()
                ->get(self::BASE_URL.'/');
            $data = $response->json();

            return [
                'available' => $response->successful() && is_array($data),
                'status' => $response->status(),
                'message' => is_array($data) ? ($data['message'] ?? null) : null,
                'docs' => is_array($data) ? ($data['docs'] ?? null) : null,
                'health' => is_array($data) ? ($data['health'] ?? null) : null,
            ];
        } catch (ConnectionException $exception) {
            report($exception);

            return [
                'available' => false,
                'status' => null,
                'message' => 'Error de conexión: '.$exception->getMessage(),
                'docs' => null,
                'health' => null,
            ];
        }
    }
    private const MAX_BYTES = 3 * 1024 * 1024;

    public function detectFromImageUrl(string $url): array
    {
        $key = trim((string) config('services.agrocloud_ai.api_key', ''));
        if ($key === '') {
            throw new RuntimeException('Falta AGROCLOUD_AI_API_KEY en el archivo .env o en las variables del entorno.');
        }

        // La URL proviene exclusivamente del registro de evidencia guardado en
        // Cloudinary por AgroCloud, nunca directamente de un campo del cliente.
        $image = Http::timeout(30)
            ->withOptions(['verify' => config('services.agrocloud_ai.ca_bundle') ?: true])
            ->get($url);
        if ($image->failed()) {
            throw new RuntimeException('No se pudo descargar la imagen almacenada para enviarla a la API de IA.');
        }

        $body = $image->body();
        if (strlen($body) > self::MAX_BYTES) {
            throw new RuntimeException('La fotografía supera el límite de 3 MiB para el endpoint directo de la API de IA.');
        }

        $contentType = (string) $image->header('Content-Type', 'image/jpeg');
        if (! str_starts_with(strtolower($contentType), 'image/')) {
            throw new RuntimeException('La URL de la evidencia no devolvió una imagen válida.');
        }

        $response = Http::timeout(110)
            ->withOptions(['verify' => config('services.agrocloud_ai.ca_bundle') ?: true])
            ->acceptJson()
            ->withHeaders(['X-API-Key' => $key])
            ->attach('file', $body, 'evidencia.jpg', ['Content-Type' => $contentType])
            ->post(self::BASE_URL.'/api/v1/detect?model_version='.self::MODEL_VERSION);

        if ($response->failed()) {
            $detail = $response->json('detail');
            $suffix = is_string($detail) && $detail !== '' ? ' Detalle: '.$detail : '';
            throw new RuntimeException('La API de IA respondió con error HTTP '.$response->status().'.'.$suffix);
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
