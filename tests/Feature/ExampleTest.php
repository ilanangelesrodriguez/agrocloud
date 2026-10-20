<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_home_shows_public_landing(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Del ingreso de la palta al dictamen de calidad,')->assertSee(route('login'))
            ->assertSee(route('acceso.create'))
            ->assertSee('data-public-menu', false)
            ->assertSee('og:description', false)
            ->assertSee('Modelo de prueba')
            ->assertSee('orugas y saltamontes')
            ->assertSee('Esquema del proceso de AgroCloud AI.');
    }
}
