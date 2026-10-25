<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\DatabaseSafety;

abstract class TestCase extends BaseTestCase
{
    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $guard = null)
    {
        // Cambiar de identidad en una prueba equivale a iniciar otra sesión.
        // Conservar el hash solo al seguir usando la misma cuenta permite probar
        // que un cambio de contraseña invalida sus sesiones anteriores.
        if ($this->app['auth']->guard($guard)->id() !== $user->getAuthIdentifier()) {
            $this->app['session']->forget('password_hash_'.($guard ?? config('auth.defaults.guard')));
        }
        $result = parent::actingAs($user, $guard);
        $this->app['session']->put('account_auth_version', (int) $user->auth_version);
        return $result;
    }

    public function createApplication()
    {
        $app = parent::createApplication();
        $config = $app['config'];
        DatabaseSafety::verify($config->get('app.env'), $config->get('database.default'), $config->get('database.connections.pgsql.url'));
        return $app;
    }
}
