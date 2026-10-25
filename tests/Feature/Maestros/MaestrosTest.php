<?php

namespace Tests\Feature\Maestros;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Rol;
use App\Models\MaestroValor;
use App\Models\Productor;
use App\Models\Lote;
use App\Models\Permiso;
use App\Services\MaestroCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaestrosTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $code = 'ADMIN', array $permissions = []): User
    {
        $company = Empresa::create(['ruc' => '201'.str_pad((string) Empresa::count(), 8, '0', STR_PAD_LEFT), 'razon_social' => 'Empresa', 'nombre_comercial' => 'Empresa', 'estado' => true]);
        $role = Rol::firstOrCreate(['codigo' => $code], ['nombre' => $code, 'estado' => true]);
        foreach ($permissions as $name) {
            $permission = Permiso::firstOrCreate(['codigo' => $name], ['nombre' => $name, 'modulo' => 'test', 'estado' => true]);
            $role->permisos()->syncWithoutDetaching([$permission->id_permiso]);
        }
        return User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $role->id_rol, 'nombre' => 'Usuario', 'correo' => 'user'.User::count().'@example.test', 'password_hash' => 'password', 'estado' => true]);
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_global_catalogs_can_be_managed_only_by_superadmin(): void
    {
        $admin = $this->account();
        $this->actingAs($admin)->postJson(route('maestros.store', 'producto'), ['nombre' => 'Café'])->assertForbidden();
        $this->get(route('maestros.show', 'producto'))->assertOk()->assertSee('Solo lectura')->assertDontSee('Acciones');
        $root = $this->account('SUPERADMIN');
        $this->actingAs($root)->postJson(route('maestros.store', 'producto'), ['nombre' => 'Café'])->assertRedirect();
        $this->assertDatabaseHas('maestro_valores', ['tipo' => 'producto', 'nombre' => 'Café', 'id_empresa' => null]);
        $this->postJson(route('maestros.store', 'producto'), ['nombre' => 'café'])->assertUnprocessable()->assertJsonValidationErrors('nombre');
        foreach (array_keys(config('maestros')) as $type) $this->get(route('maestros.show', $type))->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_company_catalogs_do_not_leak_or_allow_cross_company_updates(): void
    {
        $first = $this->account();
        $second = $this->account();
        $this->actingAs($first)->postJson(route('maestros.store', 'categoria'), ['nombre' => 'Exportación A'])->assertRedirect();
        $item = MaestroValor::where('tipo', 'categoria')->where('nombre', 'Exportación A')->firstOrFail();
        $this->actingAs($second)->get(route('maestros.show', 'categoria'))->assertOk()->assertDontSee('Exportación A');
        $this->putJson(route('maestros.update', ['categoria', $item]), ['nombre' => 'Cambiada'])->assertForbidden();
        $this->patchJson(route('maestros.toggle', ['categoria', $item]))->assertForbidden();
        $this->assertFalse(app(MaestroCatalog::class)->options('categoria', $second->id_empresa)->contains('nombre', 'Exportación A'));
        $this->postJson(route('maestros.store', 'categoria'), ['nombre' => 'Exportación A'])->assertRedirect();
    }

    public function test_shared_options_can_be_extended_and_disabled_per_company(): void
    {
        $first = $this->account();
        $second = $this->account();
        $global = MaestroValor::where('tipo', 'defecto')->whereNull('id_empresa')->firstOrFail();
        $this->actingAs($first)->patchJson(route('maestros.toggle', ['defecto', $global]))->assertRedirect();
        $catalog = app(MaestroCatalog::class);
        $this->assertFalse($catalog->options('defecto', $first->id_empresa)->contains('id', $global->id));
        $this->assertTrue($catalog->options('defecto', $second->id_empresa)->contains('id', $global->id));
        $this->postJson(route('maestros.store', 'cargo'), ['nombre' => 'Jefe de fundo'])->assertRedirect();
        $this->assertTrue($catalog->options('cargo', $first->id_empresa)->contains('nombre', 'Jefe de fundo'));
        $this->assertFalse($catalog->options('cargo', $second->id_empresa)->contains('nombre', 'Jefe de fundo'));
    }

    public function test_used_options_cannot_be_renamed_and_deactivation_keeps_history(): void
    {
        $root = $this->account('SUPERADMIN');
        $product = MaestroValor::where('tipo', 'producto')->where('nombre', 'Palta')->firstOrFail();
        Lote::create(['id_empresa' => $root->id_empresa, 'codigo_lote' => 'HISTORICO', 'producto' => 'PALTA', 'peso_kg' => 10, 'fecha_recepcion' => now(), 'estado' => 'RECIBIDO']);
        $this->actingAs($root)->putJson(route('maestros.update', ['producto', $product]), ['nombre' => 'Otro producto'])->assertUnprocessable();
        $this->patchJson(route('maestros.toggle', ['producto', $product]))->assertRedirect();
        $this->assertDatabaseHas('lote', ['codigo_lote' => 'HISTORICO', 'producto' => 'PALTA']);
        $data = app(MaestroCatalog::class)->validate(['producto' => 'Palta'], ['producto' => 'producto'], $root->id_empresa, ['producto' => 'Palta']);
        $this->assertSame('Palta', $data['producto']);
    }

    public function test_product_variety_relationship_and_weight_conversion_are_validated(): void
    {
        $catalog = app(MaestroCatalog::class);
        $data = $catalog->validate(['producto' => 'Palta', 'variedad' => 'Hass'], ['producto' => 'producto', 'variedad' => 'variedad'], null);
        $this->assertSame('Hass', $data['variedad']);
        $this->assertSame(1.5, $catalog->pesoKg(1500, 'Gramo'));
        $this->assertSame(1500.0, $catalog->pesoKg(1.5, 'Tonelada'));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $catalog->validate(['producto' => 'Mango', 'variedad' => 'Hass'], ['producto' => 'producto', 'variedad' => 'variedad'], null);
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_ubigeo_is_read_only_and_non_admin_roles_cannot_manage_catalogs(): void
    {
        $admin = $this->account();
        $this->actingAs($admin)->followingRedirects()->get(route('maestros.show', ['tipo' => 'ubigeo', 'q' => '150122']))->assertOk()->assertSee('MIRAFLORES');
        $this->postJson(route('maestros.store', 'ubigeo'), ['nombre' => 'Distrito inventado'])->assertForbidden();
        $inspector = $this->account('INSPECTOR');
        $this->actingAs($inspector)->get(route('maestros.index'))->assertForbidden();
    }

    public function test_login_returns_json_destination_and_errors_without_full_page_response(): void
    {
        $user = $this->account('SUPERADMIN');
        $this->get('/login')->assertOk()->assertSee('data-ajax-form', false)->assertSee('livewire', false);
        $this->postJson('/login', ['correo' => $user->correo, 'password' => 'incorrecta'])->assertUnprocessable()->assertJsonValidationErrors('correo');
        $this->postJson('/login', ['correo' => $user->correo, 'password' => 'password'])->assertOk()->assertJsonPath('redirect', route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->postJson('/logout')->assertOk()->assertJsonPath('redirect', route('login'));
        $this->assertGuest();
    }

    public function test_catalog_fields_render_and_lot_weight_is_saved_in_kilograms(): void
    {
        $root = $this->account('RECEPCION', ['lotes.ver', 'lotes.crear', 'lotes.editar']);
        $producer = Productor::create(['id_empresa' => $root->id_empresa, 'ruc' => '20123456789', 'nombre_razon_social' => 'Productor']);
        $this->actingAs($root)->get(route('lotes.create'))->assertOk()->assertSee('data-master-kind="producto"', false);
        $this->postJson(route('lotes.store'), ['id_productor' => $producer->id_productor, 'codigo_lote' => 'CONVERSION', 'producto' => 'Palta', 'variedad' => 'Hass', 'peso_kg' => 1500, 'unidad_medida' => 'Gramo', 'fecha_recepcion' => now()->format('Y-m-d'), 'estado' => 'RECIBIDO'])->assertRedirect();
        $lot = Lote::where('codigo_lote', 'CONVERSION')->firstOrFail();
        $this->assertSame('1.50', $lot->peso_kg);
        $this->assertSame('Gramo', $lot->unidad_medida);
        $this->get(route('lotes.edit', $lot->id_lote))->assertOk()->assertSee('value="1500"', false);
        $this->actingAs($this->account('SUPERADMIN'));
        foreach (['admin.empresas.index', 'roles.index', 'permisos.index', 'permisos.create'] as $route) $this->get(route($route))->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_inline_master_creation_returns_the_option_and_enforces_permissions(): void
    {
        $root = $this->account('SUPERADMIN');
        $this->actingAs($root)->postJson(route('maestros.store-option', 'grupo'), ['nombre' => 'Grupo del Valle'])->assertCreated()->assertJsonPath('option.nombre', 'Grupo del Valle');
        $this->postJson(route('maestros.store-option', 'grupo'), ['nombre' => 'grupo del valle'])->assertUnprocessable()->assertJsonValidationErrors('nombre');
        $this->postJson(route('maestros.store-option', 'variedad'), ['nombre' => 'Nueva variedad', 'parent_name' => 'Palta'])->assertCreated()->assertJsonPath('option.parent', 'Palta');
        $this->postJson(route('maestros.store-option', 'variedad'), ['nombre' => 'Sin producto'])->assertUnprocessable()->assertJsonValidationErrors('parent_name');
        $this->get(route('admin.empresas.create'))->assertOk()->assertSee('data-master-create', false)->assertSee('data-master-dialog', false);
        $admin = $this->account();
        $this->actingAs($admin)->postJson(route('maestros.store-option', 'grupo'), ['nombre' => 'Otro grupo'])->assertForbidden();
        $this->postJson(route('maestros.store-option', 'categoria'), ['nombre' => 'Categoría local'])->assertCreated();
        $this->assertDatabaseHas('maestro_valores', ['tipo' => 'categoria', 'nombre' => 'Categoría local', 'id_empresa' => $admin->id_empresa]);
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
    public function test_theme_is_saved_only_for_the_current_user_and_rendered_from_database(): void
    {
        $user = $this->account('SUPERADMIN');
        $other = $this->account();
        $this->actingAs($user)->putJson(route('perfil.tema.update'), ['theme' => 'dark', 'id_usuario' => $other->id_usuario])->assertOk()->assertJsonPath('theme', 'dark');
        $this->assertSame('dark', $user->fresh()->theme_preference);
        $this->assertSame('system', $other->fresh()->theme_preference);
        $this->get(route('dashboard'))->assertOk()->assertSee('data-theme-preference="dark"', false);
        $this->putJson(route('perfil.tema.update'), ['theme' => 'invalid'])->assertUnprocessable();
        $this->assertSame('dark', $user->fresh()->theme_preference);
        $this->putJson(route('perfil.tema.update'), ['theme' => 'light'])->assertOk();
        $this->assertSame('light', $user->fresh()->theme_preference);
    }
}
