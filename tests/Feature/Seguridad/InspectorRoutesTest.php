<?php

namespace Tests\Feature\Seguridad;

use Tests\TestCase;

class InspectorRoutesTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_muestreos_requires_authentication(): void
    {
        $this->get('/muestreos')->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_inspecciones_requires_authentication(): void
    {
        $this->get('/inspecciones')->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_calidad_requires_authentication(): void
    {
        $this->get('/calidad/pendientes')->assertRedirect(route('login'));
    }
}
