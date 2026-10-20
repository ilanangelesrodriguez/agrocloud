<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyValidationTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $roleCode = 'ADMIN'): User
    {
        $company = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Empresa SAC', 'nombre_comercial' => 'Mi empresa', 'estado' => true]);
        $role = Rol::create(['codigo' => $roleCode, 'nombre' => $roleCode, 'estado' => true]);
        foreach (['empresa.ver', 'empresa.editar'] as $code) {
            $permission = Permiso::create(['codigo' => $code, 'nombre' => $code, 'modulo' => 'EMPRESA', 'estado' => true]);
            $role->permisos()->attach($permission);
        }
        return User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $role->id_rol, 'nombre' => 'Admin', 'correo' => 'admin@example.test', 'password_hash' => 'secret', 'estado' => true]);
    }

    public function test_company_accepts_bare_domain_and_formatted_phone_and_preserves_tenant_state(): void
    {
        $user = $this->account();
        $this->actingAs($user)->putJson('/empresa', ['ruc' => '20123456789', 'razon_social' => 'Empresa SAC', 'nombre_comercial' => 'Mi empresa', 'sitio_web' => ' misitio.com/contacto ', 'telefono' => '(01) 234-5678', 'estado' => false])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('empresa', ['id_empresa' => $user->id_empresa, 'sitio_web' => 'https://misitio.com/contacto', 'telefono' => '(01) 234-5678', 'estado' => true]);
    }

    public function test_admin_creation_uses_the_same_normalization(): void
    {
        $user = $this->account('SUPERADMIN');
        $this->actingAs($user)->postJson('/admin/empresas', ['ruc' => '20987654321', 'razon_social' => 'Nueva SAC', 'nombre_comercial' => 'Nueva', 'sitio_web' => 'www.misitio.com', 'telefono' => '+51 999 888 777', 'estado' => true])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('empresa', ['ruc' => '20987654321', 'sitio_web' => 'https://www.misitio.com']);
    }

    public function test_validation_errors_are_in_spanish_and_unsafe_urls_and_long_phones_are_rejected(): void
    {
        $this->actingAs($this->account());
        foreach (['en', 'es'] as $locale) {
            app()->setLocale($locale);
            $response = $this->putJson('/empresa', ['ruc' => '123', 'razon_social' => 'Empresa', 'nombre_comercial' => 'Empresa', 'sitio_web' => 'javascript:alert(1)', 'telefono' => '1234567890123456']);
            $response->assertUnprocessable()->assertJsonValidationErrors(['ruc', 'sitio_web', 'telefono']);
            $this->assertSame('El campo RUC debe contener exactamente 11 dígitos.', $response->json('errors.ruc.0'));
        }
    }

    public function test_sidebar_uses_only_current_company_logo_and_has_fallback(): void
    {
        $user = $this->account();
        $user->empresa->update(['logo_url' => 'https://example.test/my-company.png']);
        $this->actingAs($user)->get('/empresa')->assertOk()->assertSee('https://example.test/my-company.png')->assertSee('Logo de Mi empresa')->assertSee('data-logo-fallback', false);
        $user->empresa->update(['logo_url' => null]);
        $user->unsetRelation('empresa');
        $this->get('/empresa')->assertOk()->assertDontSee('https://example.test/my-company.png');
    }
}
