<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        if ($app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:'
            || $app['config']->get('database.connections.sqlite.url')) {
            throw new \RuntimeException('Pruebas bloqueadas: solo se permite SQLite en memoria para proteger los datos de la aplicación.');
        }

        return $app;
    }
}
