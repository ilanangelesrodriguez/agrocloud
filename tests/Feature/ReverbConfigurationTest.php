<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ReverbConfigurationTest extends TestCase
{
    public function test_production_uses_public_https_endpoint_and_restricts_origins(): void
    {
        $originalEnv = $_ENV;
        $originalServer = $_SERVER;

        try {
            foreach (['APP_ENV' => 'production', 'APP_URL' => 'https://agrocloud-ilan.vercel.app', 'REVERB_HOST' => '127.0.0.1', 'REVERB_PORT' => '8080', 'REVERB_SCHEME' => 'http'] as $key => $value) {
                $_ENV[$key] = $_SERVER[$key] = $value;
            }

            $broadcasting = require config_path('broadcasting.php');
            $reverb = require config_path('reverb.php');

            $this->assertSame('agrocloud-ilan.vercel.app', $broadcasting['connections']['reverb']['client']['host']);
            $this->assertSame(443, $broadcasting['connections']['reverb']['client']['port']);
            $this->assertSame('https', $broadcasting['connections']['reverb']['client']['scheme']);
            $this->assertSame('127.0.0.1', $broadcasting['connections']['reverb']['options']['host']);
            $this->assertSame(['agrocloud-ilan.vercel.app'], $reverb['apps']['apps'][0]['allowed_origins']);
        } finally {
            $_ENV = $originalEnv;
            $_SERVER = $originalServer;
        }
    }

    public function test_layout_exposes_public_configuration_without_broadcast_secret(): void
    {
        $this->withoutVite();
        config([
            'navigation.sidebar' => [],
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'public-test-key',
            'broadcasting.connections.reverb.secret' => 'private-test-secret',
            'broadcasting.connections.reverb.client' => [
                'host' => 'agrocloud-ilan.vercel.app', 'port' => 443, 'scheme' => 'https',
            ],
        ]);

        $html = Blade::render('<x-layouts.app />');

        $this->assertStringContainsString('<meta name="reverb-key" content="public-test-key">', $html);
        $this->assertStringContainsString('<meta name="reverb-host" content="agrocloud-ilan.vercel.app">', $html);
        $this->assertStringNotContainsString('private-test-secret', $html);
    }

    public function test_layout_does_not_enable_echo_when_broadcasting_is_disabled(): void
    {
        $this->withoutVite();
        config(['navigation.sidebar' => [], 'broadcasting.default' => 'log']);

        $this->assertStringNotContainsString('name="reverb-key"', Blade::render('<x-layouts.app />'));
    }
}
