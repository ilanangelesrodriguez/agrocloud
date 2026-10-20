<?php

namespace Tests\Feature;

use App\Livewire\SolicitudesAcceso;
use App\Models\{Rol, User, SolicitudAcceso};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SolicitudesAccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_templates_render_escaped_company_data_and_activation_link(): void
    {
        $s = new SolicitudAcceso(['nombre' => 'Ana', 'nombre_comercial' => '<script>alert(1)</script>', 'ruc' => '20123456789', 'correo' => 'ana@example.test', 'motivo' => '<b>Datos incompletos</b>']);
        $html = view('emails.invitacion-empresa', ['s' => $s, 'url' => 'https://example.test/activar'])->render();
        $this->assertStringContainsString('https://example.test/activar', $html);
        $this->assertStringContainsString('72 horas', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $html = view('emails.solicitud-rechazada', compact('s'))->render();
        $this->assertStringContainsString('&lt;b&gt;Datos incompletos&lt;/b&gt;', $html);
    }

    public function test_review_updates_the_record_without_a_redirect_and_preserves_validation(): void
    {
        $rol = Rol::create(['codigo' => 'SUPERADMIN', 'nombre' => 'Superadmin', 'estado' => true]);
        $user = User::create(['id_rol' => $rol->id_rol, 'nombre' => 'Admin', 'correo' => 'root@example.test', 'password_hash' => 'password', 'estado' => true]);
        $this->actingAs($user);
        $s = SolicitudAcceso::create(['ruc' => '20123456789', 'razon_social' => 'Empresa de prueba', 'nombre_comercial' => 'Empresa de prueba', 'tipo_empresa' => 'PRODUCTORA', 'nombre' => 'Ana', 'apellido' => 'Perez', 'correo' => 'ana@example.test', 'telefono' => '999888777', 'uso' => 'Control de calidad de lotes agrícolas', 'estado' => 'PENDIENTE', 'historial' => []]);
        $url = route('admin.solicitudes.decidir', $s->id);
        $this->postJson($url, ['decision' => 'EN_REVISION'])->assertUnprocessable()->assertJsonValidationErrors('motivo');
        $response = $this->postJson($url, ['decision' => 'EN_REVISION', 'motivo' => 'Verificación de información empresarial.']);
        $response->assertOk()->assertJsonPath('updated', true)->assertJsonPath('tone', 'success')->assertJsonMissingPath('redirect');
        $this->assertStringContainsString('data-access-review', $response->json('html'));
        $this->assertSame('EN_REVISION', $s->fresh()->estado);
        $this->postJson(route('admin.solicitudes.decidir', $s->id), ['decision' => 'RECHAZADA', 'motivo' => 'Datos incompletos.'])->assertOk()->assertJsonPath('tone', 'error');
        $this->postJson(route('admin.solicitudes.reenviar', $s->id))->assertOk()->assertJsonPath('updated', true)->assertJsonPath('tone', 'error');
        $this->assertSame('RECHAZADA', $s->fresh()->estado);
        Livewire::test(SolicitudesAcceso::class)->call('limpiar')->assertSet('estado', '')->assertSet('buscar', '')->assertDontSee('Limpiar filtros')->set('estado', 'PENDIENTE')->assertSee('Limpiar filtros')->call('limpiar')->assertDontSee('Limpiar filtros');
    }
}
