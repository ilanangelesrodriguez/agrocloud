<?php

namespace Tests\Feature\Operaciones;

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
        foreach (EvidenciaInspeccion::where('id_inspeccion', $i->id_inspeccion)->get() as $evidencia) {
            $this->actingAs($inspector)
                ->post(route('inspecciones.revision.store', [$i->id_inspeccion, $evidencia->id_evidencia]), [
                    'conclusion' => 'SIN_HALLAZGOS',
                    'revision_completa' => '1',
                ])->assertRedirect();
        }
        $this->actingAs($inspector)->post('/inspecciones/'.$i->id_inspeccion.'/finalizar')->assertRedirect();
        $this->assertSame('PENDIENTE_DICTAMEN', $m->fresh()->estado);
        
        $supervisor = $this->user('SUPERVISOR', $company);

        $this->actingAs($supervisor)
            ->post('/calidad/inspecciones/'.$i->id_inspeccion.'/dictamen', [
                'decision' => 'APROBADO',
                'observaciones' => 'Revisión completa.',
                'protocolo_calidad' => 'Protocolo de control de calidad v1',
                'revision_independiente' => '1',
            ])
            ->assertRedirect(route('calidad.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('COMPLETADO', $m->fresh()->estado);
        $this->assertSame('CLASIFICADO', $lot->fresh()->estado->value);

        $this->assertDatabaseHas('eventos_trazabilidad', ['id_lote' => $lot->id_lote, 'entidad' => 'DictamenCalidad', 'id_usuario' => $supervisor->id_usuario]);
        foreach (['recepciones', 'muestreos', 'calidad'] as $tipo) {
            $this->get('/reportes/operativos?tipo='.$tipo)->assertOk()->assertSee('LOTE-001');
        }
    }

    #[\PHPUnit\Framework\Attributes\Group('seguridad')]
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
    public function test_large_sample_generates_all_fruit_in_one_database_insert(): void
    {
        $company = $this->company('20777777777');
        $inspector = $this->user('INSPECTOR', $company);
        $lot = $this->lot($company, 'LOTE-GRANDE');
        \App\Models\Recepcion::create(['id_empresa' => $company->id_empresa, 'id_lote' => $lot->id_lote,
            'id_usuario' => $inspector->id_usuario, 'documento' => 'G-001', 'peso_recibido_kg' => 100, 'recibido_en' => now()]);
        \Illuminate\Support\Facades\DB::enableQueryLog();
        try {
            $this->actingAs($inspector)->post('/muestreos', [
                'id_lote' => $lot->id_lote, 'tamano_muestra' => 100000, 'confirmar_carga' => '1', 'metodo' => 'Aleatorio simple',
                'protocolo' => 'Protocolo de prueba grande', 'criterio_muestra' => 'Cobertura del lote completo',
                'seleccion_frutos' => 'Selección aleatoria distribuida',
            ])->assertRedirect();
            $inserts = collect(\Illuminate\Support\Facades\DB::getQueryLog())->filter(fn ($q) => str_contains(strtolower($q['query']), 'insert into frutos_muestra'));
            $this->assertCount(1, $inserts);
        } finally {
            \Illuminate\Support\Facades\DB::disableQueryLog();
            \Illuminate\Support\Facades\DB::flushQueryLog();
        }
        $sample = Muestreo::where('id_lote', $lot->id_lote)->firstOrFail();
        $this->assertSame(100000, $sample->frutos()->count());
        $this->assertSame('M'.$sample->id_muestreo.'-F100000', $sample->frutos()->where('numero', 100000)->firstOrFail()->codigo);
        $this->assertSame('EN_MUESTREO', $lot->fresh()->estado->value);
    }

    public function test_reception_preserves_planned_arrival_and_requires_measured_weight(): void
    {
        $company = $this->company('20666111222');
        $receiver = $this->user('RECEPCION', $company);
        $lot = $this->lot($company, 'PLANIFICADO');
        $planned = now()->addDay()->startOfMinute();
        $lot->update(['estado' => 'PENDIENTE_RECEPCION', 'fecha_llegada_prevista' => $planned]);
        $this->actingAs($receiver)->get(route('recepciones.revisar', $lot->id_lote))->assertOk()->assertSee('Peso verificado al recibir');
        $data = ['documento' => 'GUIA-001', 'tipo_documento' => 'Guía de remisión', 'recibido_en' => now('America/Lima')->subHour()->format('Y-m-d H:i:s'), 'timezone' => 'America/Lima'];
        $this->post(route('recepciones.store', $lot->id_lote), $data)->assertSessionHasErrors('peso_recibido_kg');
        $this->post(route('recepciones.store', $lot->id_lote), $data + ['peso_recibido_kg' => 95])->assertSessionHasErrors('observaciones');
        $this->post(route('recepciones.store', $lot->id_lote), $data + ['peso_recibido_kg' => 95, 'observaciones' => 'Diferencia verificada al pesar.'])->assertSessionHasNoErrors();
        $this->assertTrue($lot->fresh()->fecha_llegada_prevista->equalTo($planned));
        $this->assertTrue($lot->fresh()->recepcion->recibido_en->lt($lot->fresh()->recepcion->created_at));
        $this->assertEqualsWithDelta(3600, $lot->fresh()->recepcion->recibido_en->diffInSeconds(now()), 60);
        $this->assertSame('Guía de remisión', $lot->fresh()->recepcion->tipo_documento);
    }

    public function test_authorized_sampling_procedure_enforces_its_limit_and_snapshot(): void
    {
        $company = $this->company('20666111333');
        $inspector = $this->user('INSPECTOR', $company);
        $lot = $this->lot($company, 'PROCEDIMIENTO');
        \App\Models\Recepcion::create(['id_empresa' => $company->id_empresa, 'id_lote' => $lot->id_lote, 'id_usuario' => $inspector->id_usuario,
            'documento' => 'GUIA-002', 'peso_recibido_kg' => 100, 'recibido_en' => now()]);
        $procedure = \App\Models\SamplingProcedure::create(['id_empresa' => $company->id_empresa, 'nombre' => 'Ingreso de palta', 'version' => '1',
            'metodo' => 'Aleatorio simple', 'criterio_muestra' => 'Criterio autorizado', 'seleccion_frutos' => 'Distribuir por sectores', 'max_frutos' => 3, 'activo' => true]);
        $data = ['id_lote' => $lot->id_lote, 'procedure_id' => $procedure->id, 'tamano_muestra' => 4, 'metodo' => 'Manipulado', 'protocolo' => 'Ignorar'];
        $this->actingAs($inspector)->post(route('muestreos.store'), $data)->assertSessionHasErrors('tamano_muestra');
        $data['tamano_muestra'] = 2;
        $this->post(route('muestreos.store'), $data)->assertSessionHasNoErrors();
        $sample = Muestreo::where('id_lote', $lot->id_lote)->firstOrFail();
        $this->assertSame('Aleatorio simple', $sample->metodo);
        $event = \Illuminate\Support\Facades\DB::table('eventos_trazabilidad')->where('entidad', 'Muestreo')->where('registro', $sample->id_muestreo)->where('evento', 'PROTOCOLO_MUESTREO')->first();
        $this->assertSame('Ingreso de palta · 1', json_decode($event->cambios, true)['protocolo']);
        $this->assertSame(2, $sample->frutos()->count());
    }

    public function test_large_sampling_requires_explicit_workload_confirmation(): void
    {
        $company = $this->company('20666111444');
        $inspector = $this->user('INSPECTOR', $company);
        $lot = $this->lot($company, 'CARGA');
        $this->actingAs($inspector)->post(route('muestreos.store'), ['id_lote' => $lot->id_lote, 'tamano_muestra' => 501,
            'metodo' => 'Aleatorio simple', 'protocolo' => 'Manual v1', 'criterio_muestra' => 'Criterio técnico', 'seleccion_frutos' => 'Selección distribuida'])->assertSessionHasErrors('confirmar_carga');
        $this->assertDatabaseMissing('muestreo', ['id_lote' => $lot->id_lote]);
    }

    public function test_documentary_observation_keeps_inspection_and_preserves_decision_history(): void
    {
        $company = $this->company('20666111555');
        $inspector = $this->user('INSPECTOR', $company);
        $supervisor = $this->user('SUPERVISOR', $company);
        $lot = $this->lot($company, 'CORRECCION');
        $sample = Muestreo::create(['id_empresa' => $company->id_empresa, 'id_lote' => $lot->id_lote, 'id_inspector' => $inspector->id_usuario,
            'tamano_muestra' => 1, 'metodo' => 'Aleatorio simple', 'estado' => 'PENDIENTE_DICTAMEN']);
        $inspection = Inspeccion::create(['id_empresa' => $company->id_empresa, 'id_muestreo' => $sample->id_muestreo,
            'id_inspector' => $inspector->id_usuario, 'estado' => 'VALIDADA', 'validada_en' => now()]);
        $revision = \Mockery::mock(\App\Services\RevisionEvidencias::class);
        $revision->shouldReceive('completa')->andReturn(true);
        $revision->shouldReceive('revisiones')->andReturn([]);
        $this->app->instance(\App\Services\RevisionEvidencias::class, $revision);
        $data = ['decision' => 'OBSERVADO', 'tipo_observacion' => 'DOCUMENTACION', 'observaciones' => 'Falta guía corregida.',
            'protocolo_calidad' => 'Calidad v1', 'revision_independiente' => '1'];
        $url = route('calidad.dictamen.store', $inspection->id_inspeccion);
        $this->actingAs($supervisor)->post($url, $data)->assertSessionHasNoErrors();
        $this->assertSame('OBSERVADO', $lot->fresh()->estado->value);
        $this->assertSame(1, $sample->inspecciones()->count());
        $data['dictamen_anterior'] = $inspection->fresh()->dictamen->id_dictamen;
        $data['decision'] = 'APROBADO';
        $this->post($url, $data)->assertSessionHasErrors('respuesta_observacion');
        $data['respuesta_observacion'] = 'Guía corregida verificada por supervisor, referencia GUIA-009.';
        $this->post($url, $data)->assertSessionHasNoErrors();
        $this->assertSame('CLASIFICADO', $lot->fresh()->estado->value);
        $this->assertSame(2, $inspection->dictamenes()->count());
        $this->assertSame('APROBADO', $inspection->fresh()->dictamen->decision);
        $this->assertSame('OBSERVADO', $inspection->dictamenes()->first()->decision);
        $this->post($url, $data)->assertUnprocessable();
    }

}
