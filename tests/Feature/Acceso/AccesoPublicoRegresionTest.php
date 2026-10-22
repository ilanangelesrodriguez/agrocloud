<?php

namespace Tests\Feature\Acceso;

use App\Models\{Empresa, MaestroValor, Rol, SolicitudAcceso, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccesoPublicoRegresionTest extends TestCase
{
    use RefreshDatabase;

    private function formulario(string $ruc, string $correo): array
    {
        return [
            'ruc' => $ruc,
            'razon_social' => 'Empresa Agrícola de Prueba SAC',
            'nombre_comercial' => 'Agro Prueba',
            'tipo_empresa' => 'Productora',
            'nombre' => 'Ana',
            'apellido' => 'Perez',
            'correo' => $correo,
            'telefono' => '999888777',
            'uso' => 'Gestión de recepción y control de calidad de palta.',
            'consentimiento' => '1',
            'sitio_contacto' => '',
        ];
    }

    private function prepararCatalogo(): void
    {
        MaestroValor::firstOrCreate(
            ['clave' => hash('sha256', 'tipo_empresa|global||productora')],
            ['tipo' => 'tipo_empresa', 'nombre' => 'Productora', 'estado' => true]
        );
    }

    public function test_un_ruc_no_genera_varios_expedientes_y_mantiene_respuesta_neutral(): void
    {
        $this->prepararCatalogo();
        $this->post(route('acceso.store'), $this->formulario('20123456789', 'Ana@Example.Test'))
            ->assertRedirect(route('acceso.recibida'));
        $this->post(route('acceso.store'), $this->formulario('20123456789', 'otra@example.test'))
            ->assertRedirect(route('acceso.recibida'));

        $this->assertDatabaseCount('solicitudes_acceso', 1);
        $this->assertDatabaseHas('solicitudes_acceso', ['ruc' => '20123456789', 'correo' => 'ana@example.test']);
    }

    public function test_un_correo_ya_usado_no_genera_solicitudes_imposibles_de_aprobar(): void
    {
        $this->prepararCatalogo();
        $this->post(route('acceso.store'), $this->formulario('20123456789', 'ana@example.test'))
            ->assertRedirect(route('acceso.recibida'));
        $this->post(route('acceso.store'), $this->formulario('20987654321', 'ANA@EXAMPLE.TEST'))
            ->assertRedirect(route('acceso.recibida'));
        $this->assertDatabaseCount('solicitudes_acceso', 1);

        $this->post(route('acceso.store'), $this->formulario('20987654321', 'segundo@example.test'))
            ->assertRedirect(route('acceso.recibida'));
        $this->assertDatabaseCount('solicitudes_acceso', 2);
    }

    public function test_una_solicitud_rechazada_no_reserva_el_correo_para_siempre(): void
    {
        $this->prepararCatalogo();
        $datos = $this->formulario('20123456789', 'ana@example.test');
        unset($datos['consentimiento'], $datos['sitio_contacto']);
        SolicitudAcceso::create($datos + ['estado' => 'RECHAZADA', 'historial' => []]);

        $this->post(route('acceso.store'), $this->formulario('20987654321', 'ana@example.test'))
            ->assertRedirect(route('acceso.recibida'));
        $this->assertDatabaseCount('solicitudes_acceso', 2);
    }

    public function test_un_usuario_existente_no_recibe_una_segunda_cuenta_empresarial(): void
    {
        $this->prepararCatalogo();
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador', 'estado' => true]);
        User::create([
            'id_rol' => $rol->id_rol,
            'nombre' => 'Ana',
            'correo' => 'ana@example.test',
            'password_hash' => 'ClaveSegura2026A',
            'estado' => true,
        ]);

        $this->post(route('acceso.store'), $this->formulario('20123456789', 'ANA@EXAMPLE.TEST'))
            ->assertRedirect(route('acceso.recibida'));
        $this->assertDatabaseCount('solicitudes_acceso', 0);
    }

    public function test_login_acepta_el_mismo_correo_aunque_el_visitante_use_mayusculas(): void
    {
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador', 'estado' => true]);
        $usuario = User::create([
            'id_rol' => $rol->id_rol,
            'nombre' => 'Ana',
            'correo' => 'ana@example.test',
            'password_hash' => 'ClaveSegura2026A',
            'estado' => true,
        ]);

        $this->post(route('login'), ['correo' => '  ANA@EXAMPLE.TEST  ', 'password' => 'ClaveSegura2026A'])
            ->assertRedirect();
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_activacion_habilita_la_cuenta_una_sola_vez_y_guarda_hash(): void
    {
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador', 'estado' => true]);
        $empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'Empresa Agrícola de Prueba SAC',
            'nombre_comercial' => 'Agro Prueba',
            'tipo_empresa' => 'Productora',
            'correo' => 'ana@example.test',
            'estado' => true,
        ]);
        $usuario = User::create([
            'id_empresa' => $empresa->id_empresa,
            'id_rol' => $rol->id_rol,
            'nombre' => 'Ana',
            'apellido' => 'Perez',
            'correo' => 'ana@example.test',
            'password_hash' => 'TemporalSinUso987654321',
            'estado' => false,
        ]);
        $token = str_repeat('a', 64);
        $solicitud = SolicitudAcceso::create([
            'ruc' => $empresa->ruc,
            'razon_social' => $empresa->razon_social,
            'nombre_comercial' => $empresa->nombre_comercial,
            'tipo_empresa' => 'Productora',
            'nombre' => 'Ana',
            'apellido' => 'Perez',
            'correo' => 'ana@example.test',
            'telefono' => '999888777',
            'uso' => 'Control de calidad de lotes agrícolas',
            'estado' => 'APROBADA',
            'id_empresa' => $empresa->id_empresa,
            'id_usuario' => $usuario->id_usuario,
            'invitacion_hash' => hash('sha256', $token),
            'invitacion_expira_en' => now()->addHours(72),
            'historial' => [],
        ]);

        $ruta = route('acceso.activar', ['solicitud' => $solicitud->id, 'token' => $token]);
        $this->post($ruta, ['password' => 'NuevaClave2026A', 'password_confirmation' => 'NuevaClave2026A'])
            ->assertRedirect(route('login'));
        $this->assertTrue($usuario->fresh()->estado);
        $this->assertTrue(Hash::check('NuevaClave2026A', $usuario->fresh()->password_hash));
        $this->assertNotNull($solicitud->fresh()->activada_en);
        $this->assertNull($solicitud->fresh()->invitacion_hash);

        $this->get(route('acceso.invitacion', ['solicitud' => $solicitud->id, 'token' => $token]))
            ->assertStatus(410);
    }
}
