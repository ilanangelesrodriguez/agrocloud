<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    /**
     * @return array{url: string, public_id: string}
     */
    public function uploadImage(UploadedFile $file, string $folder): array
    {
        $credentials = $this->credentials();
        $timestamp = time();
        $params = [
            'folder' => trim($folder, '/'),
            'timestamp' => $timestamp,
        ];

        $response = Http::timeout(30)
            ->withOptions(['verify' => config('services.cloudinary.ca_bundle') ?: true])
            ->attach('file', fopen($file->getRealPath(), 'rb'), $file->getClientOriginalName())
            ->post($this->apiUrl($credentials['cloud_name'], 'upload'), [
                ...$params,
                'api_key' => $credentials['api_key'],
                'signature' => $this->signature($params, $credentials['api_secret']),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Cloudinary rechazó la carga de la imagen.');
        }

        $data = $response->json();

        if (! isset($data['secure_url'], $data['public_id'])) {
            throw new RuntimeException('Cloudinary no devolvió una respuesta válida.');
        }

        return [
            'url' => $data['secure_url'],
            'public_id' => $data['public_id'],
        ];
    }

    public function deleteImage(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        $credentials = $this->credentials();
        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        Http::timeout(20)
            ->withOptions(['verify' => config('services.cloudinary.ca_bundle') ?: true])
            ->asForm()
            ->post($this->apiUrl($credentials['cloud_name'], 'destroy'), [
                ...$params,
                'api_key' => $credentials['api_key'],
                'signature' => $this->signature($params, $credentials['api_secret']),
            ])
            ->throw();
    }

    /**
     * @return array{cloud_name: string, api_key: string, api_secret: string}
     */
    private function credentials(): array
    {
        $url = (string) config('services.cloudinary.url');

        if ($url === '') {
            throw new RuntimeException('CLOUDINARY_URL no está configurado.');
        }

        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['host']) || empty($parts['user']) || empty($parts['pass'])) {
            throw new RuntimeException('CLOUDINARY_URL no tiene el formato esperado.');
        }

        return [
            'cloud_name' => $parts['host'],
            'api_key' => urldecode($parts['user']),
            'api_secret' => urldecode($parts['pass']),
        ];
    }

    /**
     * @param  array<string, int|string>  $params
     */
    private function signature(array $params, string $apiSecret): string
    {
        ksort($params);

        $payload = collect($params)
            ->map(fn (int|string $value, string $key) => "{$key}={$value}")
            ->implode('&');

        return sha1($payload.$apiSecret);
    }

    private function apiUrl(string $cloudName, string $action): string
    {
        return "https://api.cloudinary.com/v1_1/{$cloudName}/image/{$action}";
    }
}
