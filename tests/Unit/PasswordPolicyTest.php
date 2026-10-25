<?php

namespace Tests\Unit;

use App\Support\PasswordPolicy;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\{ArrayLoader, Translator};
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

class PasswordPolicyTest extends TestCase
{
    public function test_password_policy_rejects_weaker_passwords_and_allows_long_phrases(): void
    {
        $previous = Container::getInstance();
        $previousFacade = Facade::getFacadeApplication();
        $container = new Container;
        $factory = new Factory(new Translator(new ArrayLoader, 'es'), $container);
        $container->instance('validator', $factory);
        $container->instance(\Illuminate\Contracts\Validation\Factory::class, $factory);
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();
        try {
            foreach (['Corta123', 'solominusculas123', 'SINMINUSCULAS123', 'SinNumerosLargos', str_repeat('A', 256).'a1'] as $password) {
                $this->assertTrue($factory->make(compact('password'), ['password' => PasswordPolicy::rules()])->fails());
            }
            $this->assertFalse($factory->make(['password' => 'Mi frase larga de acceso 2026'], ['password' => PasswordPolicy::rules()])->fails());
        } finally {
            Container::setInstance($previous);
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($previousFacade);
        }
    }
}
