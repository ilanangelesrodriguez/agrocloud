<?php

namespace Tests\Unit;

use App\Services\SunatRucService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SunatRucServiceTest extends TestCase
{
    private $previous;
    private $previousFacade;
    private Container $container;
    private SunatRucService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous = Container::getInstance();
        $this->previousFacade = Facade::getFacadeApplication();
        $this->container = new Container;
        Container::setInstance($this->container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->container);
        $this->container->instance('config', new Config(['services' => ['ruc' => ['url' => 'https://zodipe.com/api/v1/ruc', 'key' => 'test-key', 'cache_seconds' => 86400]]]));
        Cache::swap(new Repository(new ArrayStore));
        Http::swap(new Factory);
        Http::preventStrayRequests();
        $this->service = new SunatRucService;
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacade);
        Container::setInstance($this->previous);
        parent::tearDown();
    }

    private function record(array $overrides = []): array
    {
        return array_replace(['ruc' => '20330791412', 'razon_social' => 'AGRÍCOLA PERÚ S.A.C.', 'estado' => 'ACTIVO', 'condicion' => 'HABIDO', 'actualizado' => '2026-10-23'], $overrides);
    }

    public function test_caches_provider_data_but_compares_each_declared_name_on_server(): void
    {
        Http::fake(['*' => Http::response($this->record())]);
        $valid = $this->service->verificar('20330791412', 'Agricola Peru SAC');
        $this->assertSame('verificado', $valid['resultado']);
        $this->assertSame('2026-10-23', $valid['actualizado']);
        $this->assertNotEmpty($valid['consultado_en']);
        $this->assertSame('inconsistente', $this->service->verificar('20330791412', 'Otra empresa')['resultado']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://zodipe.com/api/v1/ruc/20330791412' && $request->hasHeader('Authorization', 'Bearer test-key'));
    }

    public static function statuses(): array
    {
        return [[401, 'pendiente'], [403, 'pendiente'], [404, 'no_encontrado'], [422, 'no_encontrado'], [429, 'pendiente'], [500, 'pendiente'], [302, 'pendiente']];
    }

    #[DataProvider('statuses')]
    public function test_classifies_errors_without_exposing_provider_body(int $status, string $expected): void
    {
        Http::fake(['*' => Http::response(['error' => 'sensitive-provider-details'], $status)]);
        $result = $this->service->verificar('20330791412', 'Test');
        $this->assertSame($expected, $result['resultado']);
        $this->assertStringNotContainsString('sensitive-provider-details', json_encode($result));
        Http::assertSentCount(1);
    }

    public function test_connection_failure_is_pending_and_can_be_retried(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->assertSame('pendiente', $this->service->verificar('20330791412', 'Test')['resultado']);
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response($this->record())]);
        $this->assertSame('verificado', $this->service->verificar('20330791412', 'Agricola Peru SAC')['resultado']);
    }

    public function test_rejects_mismatched_or_malformed_responses(): void
    {
        foreach ([['ruc' => '99999999999'], ['razon_social' => null], ['razon_social' => ['unexpected']]] as $override) {
            Http::swap(new Factory);
            Http::preventStrayRequests();
            Http::fake(['*' => Http::response($this->record($override))]);
            $this->assertSame('pendiente', $this->service->verificar('20330791412', 'Test')['resultado']);
        }
    }

    public function test_missing_key_and_invalid_format_make_no_http_requests(): void
    {
        $this->container['config']->set('services.ruc.key', '');
        $this->assertSame('pendiente', $this->service->verificar('20330791412', 'Test')['resultado']);
        $this->assertSame('inconsistente', $this->service->verificar('123', 'Test')['resultado']);
        Http::assertNothingSent();
    }

    public function test_tax_status_is_separate_from_name_match(): void
    {
        Http::fake(['*' => Http::response($this->record(['estado' => 'BAJA DEFINITIVA', 'condicion' => 'NO HABIDO']))]);
        $result = $this->service->verificar('20330791412', 'Agricola Peru SAC');
        $this->assertSame('verificado', $result['resultado']);
        $this->assertSame('BAJA DEFINITIVA', $result['estado']);
        $this->assertStringContainsString('Revisa el estado', $result['mensaje']);
    }
}
