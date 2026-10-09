<?php

namespace Tests\Feature;

use Tests\TestCase;

class InspectorRoutesTest extends TestCase
{
    public function test_muestreos_requires_authentication(): void
    {
        $this->get('/muestreos')->assertRedirect(route('login'));
    }

    public function test_inspecciones_requires_authentication(): void
    {
        $this->get('/inspecciones')->assertRedirect(route('login'));
    }

    public function test_calidad_requires_authentication(): void
    {
        $this->get('/calidad/pendientes')->assertRedirect(route('login'));
    }
}
