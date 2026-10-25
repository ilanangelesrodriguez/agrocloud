<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SunatRucService
{
    public function consultar(string $ruc): array
    {
        $pending = fn (string $message) => ['ruc' => $ruc, 'resultado' => 'pendiente', 'mensaje' => $message];
        if (! preg_match('/^[0-9]{11}$/D', $ruc)) {
            return ['ruc' => $ruc, 'resultado' => 'inconsistente', 'mensaje' => 'El RUC debe tener 11 dígitos.'];
        }

        $url = rtrim((string) config('services.ruc.url'), '/');
        $key = (string) config('services.ruc.key');
        if ($key === '' || ! str_starts_with($url, 'https://')) {
            return $pending('Consulta tributaria pendiente: servicio no configurado.');
        }

        $cacheKey = 'sunat:ruc:v1:'.hash('sha256', $url.'|'.$key.'|'.$ruc);
        try {
            $cached = Cache::get($cacheKey);
        } catch (\Exception) {
            return $pending('Consulta pendiente: el almacenamiento temporal no está disponible.');
        }
        if (is_array($cached)) {
            return $cached;
        }

        try {
            // Do not retry quota/authentication errors or forward credentials on redirects.
            $response = Http::withToken($key)->acceptJson()->connectTimeout(2)->timeout(6)
                ->withOptions(['verify' => config('services.ruc.ca_bundle') ?: true])
                ->withoutRedirecting()->get($url.'/'.$ruc);
        } catch (ConnectionException) {
            return $pending('No se pudo conectar con el proveedor. Puedes continuar y verificar después.');
        }

        if (in_array($response->status(), [404, 422], true)) {
            $result = ['ruc' => $ruc, 'resultado' => 'no_encontrado', 'mensaje' => 'El proveedor no reconoce este RUC en su padrón. Revisa el número.'];
            $this->cache($cacheKey, $result, 60);
            return $result;
        }
        if (! $response->successful()) {
            return $pending(match ($response->status()) {
                401, 403 => 'Consulta pendiente: revisa las credenciales del proveedor.',
                429 => 'Consulta pendiente: el proveedor alcanzó su límite de consultas.',
                default => 'Consulta pendiente: el proveedor no está disponible.',
            });
        }

        $data = $response->json();
        if (! is_array($data) || ($data['ruc'] ?? null) !== $ruc
            || ! is_string($data['razon_social'] ?? null) || trim($data['razon_social']) === ''
            || mb_strlen($data['razon_social']) > 200) {
            return $pending('Consulta pendiente: el proveedor devolvió una respuesta inesperada.');
        }

        $result = ['ruc' => $ruc, 'razon_social' => trim($data['razon_social']), 'resultado' => 'verificado',
            'consultado_en' => now()->toIso8601String(), 'fuente' => 'Zodipe / padrón SUNAT'];
        foreach (['estado', 'condicion', 'direccion', 'ubigeo', 'actualizado'] as $field) {
            $result[$field] = is_string($data[$field] ?? null) ? mb_substr($data[$field], 0, 500) : null;
        }
        $this->cache($cacheKey, $result, max(60, (int) config('services.ruc.cache_seconds', 86400)));
        return $result;
    }

    private function cache(string $key, array $result, int $seconds): void
    {
        try {
            Cache::put($key, $result, $seconds);
        } catch (\Exception) {
            // A cache write failure must not discard an otherwise valid lookup.
        }
    }

    public function verificar(string $ruc, string $name): array
    {
        $result = $this->consultar($ruc);
        $result['intentado_en'] = now()->toIso8601String();
        $result['nombre_declarado'] = $name;
        if ($result['resultado'] === 'verificado') {
            $normalize = fn (string $value) => preg_replace('/[^A-Z0-9]/', '', strtoupper(Str::ascii($value)));
            $matches = $normalize($name) === $normalize($result['razon_social']);
            $result['resultado'] = $matches ? 'verificado' : 'inconsistente';
            $result['mensaje'] = $matches ? 'La razón social coincide con el padrón.' : 'La razón social declarada difiere del padrón. Revisa los datos.';
            if ($result['estado'] !== 'ACTIVO' || $result['condicion'] !== 'HABIDO') {
                $result['mensaje'] .= ' Revisa el estado y la condición tributaria.';
            }
        }
        return $result;
    }
}
