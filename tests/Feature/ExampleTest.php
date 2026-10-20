<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_home_shows_public_landing(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Calidad que se ve.')->assertSee(route('login'));
    }
}
