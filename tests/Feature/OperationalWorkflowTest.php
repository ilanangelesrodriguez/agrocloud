<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvidenciaInspeccion;
use App\Models\Inspeccion;
use App\Models\Lote;
use App\Models\Muestreo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $code, Empresa $company): User
    {
        $role = Rol::firstOrCreate(['codigo' => $code], ['nombre' => $code, 'estado' => true]);
        foreach (['dashboard.ver', 'recepciones.ver', 'recepciones.crear', 'trazabilidad.ver', 'reportes.operativos', 'muestreos.crear', 'muestreos.ver', 'inspecciones.crear', 'inspecciones.ver', 'inspecciones.validar', 'calidad.ver', 'calidad.clasificar', 'lotes.ver'] as $p) {
            $permission = Permiso::firstOrCreate(['codigo' => $p], ['nombre' => $p, 'modulo' => 'OPERACIONES', 'estado' => true]);
            $role->permisos()->syncWithoutDetaching([$permission->id_permiso]);
        }

        return User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $role->id_rol, 'nombre' => $code, 'correo' => $code.$company->id_empresa.'@test.local', 'password_hash' => 'secret', 'estado' => true]);
    }

    private function company(string $ruc): Empresa
    {
        return Empresa::create(['ruc' => $ruc, 'razon_social' => 'Empresa', 'nombre_comercial' => 'Empresa', 'estado' => true]);
    }

    private function lot(Empresa $company, string $code): Lote
    {
        return Lote::create(['id_empresa' => $company->id_empresa, 'codigo_lote' => $code, 'producto' => 'Palta', 'peso_kg' => 100, 'fecha_recepcion' => now(), 'estado' => 'RECIBIDO']);
    }

    public function test_reception_sample_identity_trace_and_missing_fruit_guard(): void
    {
        $company = $this->company('20123456789');
        $receiver = $this->user('RECEPCION', $company);
        $inspector = $this->user('INSPECTOR', $company);
        $lot = $this->lot($company, 'LOTE-001');
        $sample = [
            'id_lote' => $lot->id_lote,
            'tamano_muestra' => 2,
            'metodo' => 'Aleatorio simple',
            'protocolo' => 'Protocolo de prueba v1',
            'criterio_muestra' => 'Muestra representativa del lote.',
            'seleccion_frutos' => 'Selección aleatoria en toda la cobertura del lote.',
        ];
        $this->actingAs($inspector)->postJson('/muestreos', $sample)->assertStatus(422);
        $receipt = [
            'documento' => 'GUIA-001',
            'peso_recibido_kg' => 95,
            'recibido_en' => now()->subMinute()->format('Y-m-d H:i:s'),
            'observaciones' => 'Se recibieron 95 kg de los 100 kg declarados. Los 5 kg restantes corresponden a una diferencia de peso registrada durante la recepción.',
        ];
        $this->actingAs($receiver)->postJson('/recepciones/'.$lot->id_lote, [
            ...$receipt,
            'observaciones' => null,
        ])->assertStatus(422)->assertJsonValidationErrors('observaciones');
        $this->actingAs($receiver)->post('/recepciones/'.$lot->id_lote, $receipt)->assertRedirect('/recepciones');
        $this->post('/recepciones/'.$lot->id_lote, $receipt)->assertStatus(422);
        $this->assertDatabaseCount('recepciones', 1);
        $this->actingAs($inspector)->post('/muestreos', $sample)->assertRedirect();
        $m = Muestreo::firstOrFail();
        $this->assertDatabaseCount('frutos_muestra', 2);
        $this->assertSame('M'.$m->id_muestreo.'-F00001', $m->frutos()->first()->codigo);
        $this->postJson('/muestreos', $sample)->assertStatus(422);
        $this->post('/muestreos/'.$m->id_muestreo.'/inspecciones')->assertRedirect();
        $i = Inspeccion::firstOrFail();
        EvidenciaInspeccion::create(['id_empresa' => $company->id_empresa, 'id_inspeccion' => $i->id_inspeccion, 'id_fruto' => $m->frutos()->first()->id, 'imagen_url' => 'https://example.test/photo.jpg', 'imagen_public_id' => 'photo', 'estado_ia' => 'COMPLETADO', 'resultado_ia' => ['detections' => []]]);
        $this->post('/inspecciones/'.$i->id_inspeccion.'/finalizar')->assertStatus(422);
        $this->get('/muestreos/'.$m->id_muestreo)->assertOk()->assertSee('F00001');
        $this->get('/trazabilidad/'.$lot->id_lote)->assertOk()->assertSee('Recepcion');
        $this->assertDatabaseHas('eventos_trazabilidad', ['id_lote' => $lot->id_lote, 'entidad' => 'Recepcion', 'id_usuario' => $receiver->id_usuario]);
        $this->assertDatabaseHas('eventos_trazabilidad', ['id_lote' => $lot->id_lote, 'entidad' => 'Lote', 'evento' => 'ACTUALIZADO']);
        EvidenciaInspeccion::create(['id_empresa' => $company->id_empresa, 'id_inspeccion' => $i->id_inspeccion, 'id_fruto' => $m->frutos()->orderByDesc('numero')->first()->id, 'imagen_url' => 'https://example.test/photo2.jpg', 'imagen_public_id' => 'photo2', 'estado_ia' => 'COMPLETADO', 'resultado_ia' => ['detections' => []]]);
        $this->post('/inspecciones/'.$i->id_inspeccion.'/finalizar')->assertRedirect();
        $this->assertSame('PENDIENTE_DICTAMEN', $m->fresh()->estado);
        $supervisor = $this->user('SUPERVISOR', $company);
        $this->actingAs($supervisor)->post('/calidad/inspecciones/'.$i->id_inspeccion.'/dictamen', ['decision' => 'APROBADO', 'observaciones' => 'Revisión completa.'])->assertRedirect();
        $this->assertSame('COMPLETADO', $m->fresh()->estado);
        $this->assertSame('CLASIFICADO', $lot->fresh()->estado->value);
        $this->assertDatabaseHas('eventos_trazabilidad', ['id_lote' => $lot->id_lote, 'entidad' => 'DictamenCalidad', 'id_usuario' => $supervisor->id_usuario]);
        foreach (['recepciones', 'muestreos', 'calidad'] as $tipo) {
            $this->get('/reportes/operativos?tipo='.$tipo)->assertOk()->assertSee('LOTE-001');
        }
    }

    public function test_company_isolation_reports_and_role_gates(): void
    {
        $a = $this->company('20123456789');
        $b = $this->company('20987654321');
        $user = $this->user('PRODUCCION', $a);
        $local = $this->lot($a, 'LOCAL-LOT');
        $foreign = $this->lot($b, 'FOREIGN-LOT');
        $this->actingAs($user)->get('/reportes/operativos')->assertOk()->assertSee('LOCAL-LOT')->assertDontSee('FOREIGN-LOT');
        $csv = $this->get('/reportes/operativos?tipo=lotes&exportar=csv')->assertOk()->streamedContent();
        $this->assertStringContainsString('LOCAL-LOT', $csv);
        $this->assertStringNotContainsString('FOREIGN-LOT', $csv);
        $this->get('/trazabilidad/'.$foreign->id_lote)->assertNotFound();
        $this->post('/recepciones/'.$local->id_lote, [])->assertForbidden();
        $this->get('/recepciones')->assertOk();
        $this->get('/dashboard')->assertOk()->assertSee('Lotes disponibles para producción');
        $this->get('/reportes/operativos?desde=2026-10-12&hasta=2026-10-01')->assertRedirect()->assertSessionHasErrors('hasta');
    }
}
