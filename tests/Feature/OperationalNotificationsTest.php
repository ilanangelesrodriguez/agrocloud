<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use App\Services\OperationalNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function company(string $ruc): Empresa
    {
        return Empresa::create(['ruc' => $ruc, 'razon_social' => 'Agro Empresa', 'nombre_comercial' => 'Agro Empresa', 'estado' => true]);
    }

    private function user(Empresa $company, string $roleCode, string $email): User
    {
        $role = Rol::firstOrCreate(['codigo' => $roleCode], ['nombre' => $roleCode, 'estado' => true]);
        return User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $role->id_rol, 'nombre' => 'Prueba',
            'correo' => $email, 'password_hash' => 'secret', 'estado' => true]);
    }

    public function test_notificacion_persistida_segregada_y_marcada_como_leida(): void
    {
        $companyA = $this->company('20111111111');
        $companyB = $this->company('20222222222');
        $inspector = $this->user($companyA, 'INSPECTOR', 'inspector@a.test');
        $receiver = $this->user($companyA, 'RECEPCION', 'recepcion@a.test');
        $foreign = $this->user($companyB, 'INSPECTOR', 'inspector@b.test');

        OperationalNotifier::roles((int) $companyA->id_empresa, ['INSPECTOR'],
            'LOTE_RECIBIDO', 'Lote recibido', 'Ya puedes muestrear', '/muestreos/crear?lote=1');

        $this->assertCount(1, $inspector->notifications);
        $this->assertCount(0, $receiver->notifications);
        $this->assertCount(0, $foreign->notifications);

        $id = $inspector->notifications()->firstOrFail()->id;
        $this->actingAs($foreign)->get('/notificaciones/feed')->assertOk()->assertJsonPath('no_leidas', 0);
        $this->get('/notificaciones/'.$id.'/abrir')->assertNotFound();
        $this->actingAs($inspector)->get('/notificaciones/feed')->assertOk()->assertJsonPath('no_leidas', 1);
        $this->post('/notificaciones/'.$id.'/leer')->assertRedirect();
        $this->get('/notificaciones/feed')->assertJsonPath('no_leidas', 0);
    }

    public function test_bandeja_no_permita_redireccion_fuera_del_sitio(): void
    {
        $company = $this->company('20333333333');
        $user = $this->user($company, 'SUPERVISOR', 'supervisor@c.test');
        OperationalNotifier::user((int) $company->id_empresa, (int) $user->id_usuario,
            'TEST', 'Revisión', 'No salir del sitio', '//evil.example/path');
        $id = $user->notifications()->firstOrFail()->id;
        $this->actingAs($user)->get('/notificaciones/'.$id.'/abrir')->assertRedirect(route('notificaciones.index'));
    }
}
