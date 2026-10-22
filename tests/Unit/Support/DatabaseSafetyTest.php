<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Tests\Support\DatabaseSafety;

class DatabaseSafetyTest extends TestCase
{
    private function withEnvironment(callable $callback): void
    {
        $values = ['CI' => 'true', 'AGROCLOUD_ALLOW_PG_TESTS' => '1', 'NEON_TEST_HOST' => 'testing.example.test', 'NEON_PROD_HOST' => 'production.example.test', 'NEON_TEST_DATABASE' => 'testing', 'NEON_TEST_USER' => 'qa', 'DB_SSLMODE' => 'require'];
        $previous = [];
        foreach ($values as $key => $value) {
            $previous[$key] = getenv($key);
            putenv($key.'='.$value);
        }
        try {
            $callback();
        } finally {
            foreach ($previous as $key => $value) {
                putenv($value === false ? $key : $key.'='.$value);
            }
        }
    }

    public function test_allows_only_the_verified_testing_identity(): void
    {
        $this->withEnvironment(function () {
            $this->assertSame(['database' => 'testing', 'user' => 'qa'], DatabaseSafety::verify('testing', 'pgsql', 'postgresql://qa:fake@testing.example.test/testing?sslmode=require'));
        });
    }

    public function test_rejects_production_wrong_identity_and_connection_overrides(): void
    {
        $this->withEnvironment(function () {
            foreach ([
                ['production', 'pgsql', 'postgresql://qa:fake@testing.example.test/testing'],
                ['testing', 'sqlite', 'postgresql://qa:fake@testing.example.test/testing'],
                ['testing', 'pgsql', 'postgresql://qa:fake@production.example.test/testing'],
                ['testing', 'pgsql', 'postgresql://other:fake@testing.example.test/testing'],
                ['testing', 'pgsql', 'postgresql://qa:fake@testing.example.test/production'],
                ['testing', 'pgsql', 'postgresql://qa:fake@testing.example.test/testing?host=production.example.test'],
                ['testing', 'pgsql', 'postgresql://qa:fake@testing.example.test/testing?sslmode=disable'],
                ['testing', 'pgsql', null],
            ] as $input) {
                try {
                    DatabaseSafety::verify(...$input);
                    $this->fail('Se permitió una conexión no verificada.');
                } catch (\RuntimeException $error) {
                    $this->assertStringContainsString('Pruebas bloqueadas', $error->getMessage());
                }
            }
        });
    }
}
