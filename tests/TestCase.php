<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $config = $app['config'];

        $url = $config->get('database.connections.pgsql.url');
        $host = $url ? parse_url($url, PHP_URL_HOST) : null;
        $database = $url ? ltrim((string) parse_url($url, PHP_URL_PATH), '/') : null;
        $username = $url ? parse_url($url, PHP_URL_USER) : null;

        $allowed = [
            $config->get('app.env') === 'testing',
            $config->get('database.default') === 'pgsql',
            getenv('CI') === 'true',
            getenv('AGROCLOUD_ALLOW_PG_TESTS') === '1',
            !empty($host),
            !empty($database),
            !empty($username),
            !empty(getenv('NEON_TEST_HOST')),
            !empty(getenv('NEON_PROD_HOST')),
            $host === getenv('NEON_TEST_HOST'),
            $host !== getenv('NEON_PROD_HOST'),
            $database === getenv('NEON_TEST_DATABASE'),
            $username === getenv('NEON_TEST_USER'),
        ];

        if (in_array(false, $allowed, true)) {
            throw new \RuntimeException(
                'Pruebas bloqueadas: PostgreSQL testing no verificado.'
            );
        }

        return $app;
    }
}
