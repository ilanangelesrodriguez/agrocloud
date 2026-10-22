<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\DatabaseSafety;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $config = $app['config'];
        DatabaseSafety::verify($config->get('app.env'), $config->get('database.default'), $config->get('database.connections.pgsql.url'));
        return $app;
    }
}
