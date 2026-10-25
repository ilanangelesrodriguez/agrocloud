<?php

namespace Tests\Unit;

use App\Services\NotificationDestination;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class NotificationDestinationTest extends TestCase
{
    public function test_accepts_internal_destinations_and_rejects_external_urls(): void
    {
        $previous = Container::getInstance();
        $container = new Container;
        Container::setInstance($container);
        $routes = new \Illuminate\Routing\RouteCollection;
        $routes->add((new \Illuminate\Routing\Route(['GET'], 'notificaciones', fn () => null))->name('notificaciones.index'));
        $url = new \Illuminate\Routing\UrlGenerator($routes, \Illuminate\Http\Request::create('https://agro.test'));
        $container->instance('url', $url);
        $container->instance(\Illuminate\Contracts\Routing\UrlGenerator::class, $url);

        try {
            foreach (['/admin/solicitudes/7', 'https://agro.test/admin/solicitudes/7'] as $url) {
                $this->assertSame('/admin/solicitudes/7', NotificationDestination::resolve($url));
            }
            $this->assertSame('/admin/solicitudes/7?tab=datos#detalle', NotificationDestination::resolve('https://agro.test/admin/solicitudes/7?tab=datos#detalle'));
            foreach (['//evil.test', 'https://evil.test/admin', 'https://agro.test.evil.test', '/\\evil.test', 'javascript:alert(1)', 'https://user@agro.test/admin'] as $url) {
                $this->assertSame('https://agro.test/notificaciones', NotificationDestination::resolve($url));
            }
        } finally {
            Container::setInstance($previous);
        }
    }
}
