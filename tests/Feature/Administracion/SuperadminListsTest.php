<?php

namespace Tests\Feature\Administracion;

use App\Livewire\AdminEmpresasIndex;
use App\Livewire\AdminEmpresaUsuariosIndex;
use App\Livewire\MaestrosList;
use App\Livewire\PermisosIndex;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperadminListsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Rol::create(['codigo' => 'SUPERADMIN', 'nombre' => 'Superadmin', 'estado' => true]);
        $user = User::create(['id_rol' => $role->id_rol, 'nombre' => 'Admin', 'correo' => 'root@example.test', 'password_hash' => 'password', 'estado' => true]);
        $this->actingAs($user);
    }

    private function company(string $name, bool $active = true): Empresa
    {
        return Empresa::create(['ruc' => '201'.str_pad((string) Empresa::count(), 8, '0', STR_PAD_LEFT), 'razon_social' => $name, 'nombre_comercial' => $name, 'estado' => $active, 'tipo_empresa' => 'AGROQUIMICOS']);
    }

    public function test_company_filters_pagination_and_exports_work_without_query_strings(): void
    {
        for ($i = 0; $i < 14; $i++) $this->company('Empresa activa '.$i);
        $this->company('Empresa inactiva', false);
        $component = Livewire::test(AdminEmpresasIndex::class)->set('estado', '1')->set('tipo', 'AGROQUIMICOS')->call('filtrar')->assertHasNoErrors()->assertDontSee('Empresa inactiva');
        $component->call('setPage', 2)->assertSet('paginators.page', 2);
        $this->assertStringNotContainsString('?page=', $component->html());
        $this->assertStringNotContainsString('?q=', $component->html());
        $csv = $this->get(route('admin.empresas.export.csv'))->assertOk()->streamedContent();
        $this->assertStringContainsString('Empresa activa', $csv);
        $this->assertStringNotContainsString('Empresa inactiva', $csv);
        $component->call('limpiar')->assertSet('paginators.page', 1)->assertSet('filters', []);
        $this->assertNull(session('admin.empresas.filters'));
    }

    public function test_old_filter_links_redirect_to_clean_urls_and_restore_filters(): void
    {
        $this->company('Objetivo');
        $this->company('Otra empresa');
        $this->get(route('admin.empresas.index', ['q' => 'Objetivo', 'estado' => '1']))->assertRedirect(route('admin.empresas.index'));
        Livewire::test(AdminEmpresasIndex::class)->assertSet('q', 'Objetivo')->assertSee('Objetivo')->assertDontSee('Otra empresa');
    }

    public function test_permissions_pagination_is_in_spanish_and_does_not_change_url(): void
    {
        for ($i = 0; $i < 25; $i++) Permiso::create(['codigo' => 'test.'.$i, 'nombre' => 'Permiso '.$i, 'modulo' => 'TEST', 'estado' => true]);
        $component = Livewire::test(PermisosIndex::class)->assertSee('Mostrando')->assertSee('resultados')->assertDontSee('Showing');
        $component->call('setPage', 2)->assertSet('paginators.page', 2);
        $this->assertStringNotContainsString('?page=', $component->html());
        $this->get(route('permisos.index', ['page' => 2]))->assertRedirect(route('permisos.index'));
    }

    public function test_master_filters_and_company_users_use_clean_pagination(): void
    {
        $component = Livewire::test(MaestrosList::class, ['catalogType' => 'ubigeo'])->set('q', '150122')->call('filtrar')->assertHasNoErrors()->assertSee('MIRAFLORES');
        $this->assertStringNotContainsString('?q=', $component->html());
        $company = $this->company('Empresa');
        $role = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador', 'estado' => true]);
        User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $role->id_rol, 'nombre' => 'Encontrable', 'correo' => 'find@example.test', 'password_hash' => 'password', 'estado' => true]);
        Livewire::test(AdminEmpresaUsuariosIndex::class, ['empresaId' => $company->id_empresa])->set('q', 'Encontrable')->call('filtrar')->assertHasNoErrors()->assertSee('Encontrable')->assertDontSee('root@example.test');
        $this->get(route('maestros.index'))
            ->assertOk()
            ->assertSee('Catálogos maestros')
            ->assertSee('Ubicaciones geográficas')
            ->assertSee('<svg', false);
    }
    public function test_permission_filters_and_conditional_clear_button(): void
    {
        Permiso::create(['codigo' => 'calidad.ver', 'nombre' => 'Consultar calidad', 'modulo' => 'CALIDAD', 'estado' => true]);
        Permiso::create(['codigo' => 'lotes.ver', 'nombre' => 'Consultar lotes', 'modulo' => 'LOTES', 'estado' => true]);
        $component = Livewire::test(PermisosIndex::class)->assertDontSee('Limpiar filtros');
        $component->set('buscar', 'calidad.ver')->assertSee('Consultar calidad')->assertDontSee('Consultar lotes')->assertSee('Limpiar filtros');
        $component->call('limpiar')->assertSet('buscar', '')->assertSet('modulo', '')->assertDontSee('Limpiar filtros');
        $component->set('modulo', 'LOTES')->assertSee('Consultar lotes')->assertDontSee('Consultar calidad');
    }
}
