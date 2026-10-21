<?php

namespace Tests\Support;

final class DatabaseSafety
{
    public static function verify(string $environment, string $driver, ?string $url): array
    {
        $parts = $url ? parse_url($url) : false;
        $host = $parts['host'] ?? '';
        $database = rawurldecode(ltrim($parts['path'] ?? '', '/'));
        $user = rawurldecode($parts['user'] ?? '');
        parse_str($parts['query'] ?? '', $query);
        foreach (['NEON_TEST_HOST', 'NEON_PROD_HOST', 'NEON_TEST_DATABASE', 'NEON_TEST_USER'] as $key) {
            if (! is_string(getenv($key)) || trim(getenv($key)) === '') {
                throw new \RuntimeException('Pruebas bloqueadas: faltan identificadores de Neon testing.');
            }
        }
        $allowed = $environment === 'testing' && $driver === 'pgsql'
            && getenv('CI') === 'true' && getenv('AGROCLOUD_ALLOW_PG_TESTS') === '1'
            && in_array($parts['scheme'] ?? '', ['postgres', 'postgresql', 'pgsql'], true)
            && $host === getenv('NEON_TEST_HOST') && $host !== getenv('NEON_PROD_HOST')
            && $database === getenv('NEON_TEST_DATABASE') && $user === getenv('NEON_TEST_USER')
            && array_diff(array_keys($query), ['sslmode', 'channel_binding']) === []
            && in_array($query['sslmode'] ?? getenv('DB_SSLMODE'), ['require', 'verify-ca', 'verify-full'], true);
        if (! $allowed) {
            throw new \RuntimeException('Pruebas bloqueadas: PostgreSQL testing no verificado.');
        }

        return ['database' => $database, 'user' => $user];
    }
}
