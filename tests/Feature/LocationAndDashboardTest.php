<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Lote;
use App\Models\Productor;
use App\Services\UbigeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LocationAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role, array $permissions): User
    {
        $company = Empresa::create(['ruc' => '201'.str_pad((string) Empresa::count(), 8, '0', STR_PAD_LEFT), 'razon_social' => $role, 'nombre_comercial' => $role, 'estado' => true]);
        $rol = Rol::create(['codigo' => $role, 'nombre' => $role, 'estado' => true]);
        foreach ($permissions as $code) {
            $permission = Permiso::firstOrCreate(['codigo' => $code], ['nombre' => $code, 'modulo' => 'test', 'estado' => true]);
            $rol->permisos()->attach($permission);
        }
        return User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $rol->id_rol, 'nombre' => $role, 'correo' => strtolower($role).'@example.test', 'password_hash' => 'password', 'estado' => true]);
    }

    public function test_dashboard_renders_for_every_role_and_honors_permissions(): void
    {
        foreach ([
            'SUPERADMIN' => [],
            'ADMIN' => ['dashboard.ver', 'usuarios.ver', 'lotes.ver'],
            'RECEPCION' => ['dashboard.ver', 'lotes.ver', 'lotes.crear', 'productores.ver'],
            'INSPECTOR' => ['dashboard.ver', 'muestreos.ver', 'inspecciones.ver'],
            'SUPERVISOR' => ['dashboard.ver', 'calidad.ver', 'inspecciones.ver'],
            'PRODUCCION' => ['dashboard.ver', 'empresa.ver'],
        ] as $role => $permissions) {
            $user = $this->account($role, $permissions);
            $response = $this->actingAs($user)->get('/dashboard')->assertOk();
            if ($role !== 'SUPERADMIN') {
                $response->assertDontSee('Incidencias abiertas');
                $response->assertViewHas('metrics', fn ($items) => collect($items)->every(fn ($item) => is_int($item['value'])));
                if (!in_array('usuarios.ver', $permissions)) $response->assertDontSee('Administrar usuarios');
            }
        }
    }

    public function test_dashboard_counts_only_the_current_company(): void
    {
        $user = $this->account('ADMIN', ['dashboard.ver', 'lotes.ver']);
        $other = $this->account('RECEPCION', ['dashboard.ver']);
        foreach ([$user, $other] as $account) {
            Lote::create(['id_empresa' => $account->id_empresa, 'codigo_lote' => 'LOTE-'.$account->id_empresa, 'producto' => 'Palta', 'peso_kg' => 10, 'fecha_recepcion' => now(), 'estado' => 'RECIBIDO']);
        }
        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertViewHas('metrics', fn ($items) => collect($items)->firstWhere('label', 'Lotes registrados')['value'] === 1)
            ->assertViewHas('recentLots', fn ($lots) => $lots->count() === 1 && $lots->first()->id_empresa === $user->id_empresa);
    }

    public function test_producer_location_is_saved_with_calculated_ubigeo(): void
    {
        $user = $this->account('RECEPCION', ['productores.editar']);
        $producer = Productor::create(['id_empresa' => $user->id_empresa, 'ruc' => '20123456789', 'nombre_razon_social' => 'Productor']);
        $this->actingAs($user)->putJson('/productores/'.$producer->id_productor, [
            'ruc' => $producer->ruc, 'nombre_razon_social' => 'Productor',
            'departamento' => 'Lima', 'provincia' => 'Lima', 'distrito' => 'Miraflores', 'ubigeo' => '000000',
        ])->assertRedirect();
        $this->assertDatabaseHas('productor', ['id_productor' => $producer->id_productor, 'departamento' => 'LIMA', 'ubigeo' => '150122']);
    }

    public function test_mismatched_location_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(UbigeoService::class)->normalize(['departamento' => 'LIMA', 'provincia' => 'AREQUIPA']);
    }

    public function test_company_renders_three_selects_with_saved_location(): void
    {
        $user = $this->account('ADMIN', ['empresa.ver', 'empresa.editar']);
        $user->empresa->update(['departamento' => 'LIMA', 'provincia' => 'LIMA', 'distrito' => 'MIRAFLORES', 'ubigeo' => '150122']);
        $response = $this->actingAs($user)->get('/empresa')->assertOk();
        $html = new \DOMDocument();
        @$html->loadHTML($response->getContent());
        $xpath = new \DOMXPath($html);
        $this->assertSame(3, $xpath->query('//select[@data-geo-field]')->length);
        $this->assertSame('LIMA', $xpath->query('//select[@name="departamento"]/option')->item(0)->getAttribute('value'));
        $this->assertSame('MIRAFLORES', $xpath->query('//select[@name="distrito"]/option')->item(0)->getAttribute('value'));
    }

    public function test_producer_forms_use_geographic_fields_without_legacy_location_input(): void
    {
        $user = $this->account('RECEPCION', ['productores.ver', 'productores.crear', 'productores.editar', 'productores.lotes.ver']);
        $producer = Productor::create(['id_empresa' => $user->id_empresa, 'ruc' => '20123456789', 'nombre_razon_social' => 'Productor', 'ubicacion' => 'Referencia anterior']);
        foreach ([route('productores.create'), route('productores.edit', $producer->id_productor)] as $url) {
            $response = $this->actingAs($user)->get($url)->assertOk();
            $html = new \DOMDocument();
            @$html->loadHTML($response->getContent());
            $xpath = new \DOMXPath($html);
            $this->assertSame(0, $xpath->query('//input[@name="ubicacion"]')->length);
            $this->assertSame(3, $xpath->query('//select[@data-geo-field]')->length);
        }
        $this->get('/productores/'.$producer->id_productor)->assertOk()->assertSee('Referencia anterior')->assertSee('Ver lotes (0)');
    }
}
