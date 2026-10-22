<?php

namespace Tests\Feature\Acceso;

use App\Models\{Empresa, Rol, SolicitudAcceso, User};
use App\Notifications\{PasswordChanged, ResetAccountPassword};
use App\Rules\UniqueUserEmail;
use App\Services\AccesoEmpresarial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Hash, Mail, Notification, Password, Validator};
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $correo = 'root@example.test', bool $active = true): User
    {
        $role = Rol::firstOrCreate(['codigo' => 'SUPERADMIN'], ['nombre' => 'Superadmin', 'estado' => true]);
        $user = User::create(['id_rol' => $role->id_rol, 'nombre' => 'Ana', 'correo' => $correo,
            'password_hash' => 'AnteriorClave2026', 'estado' => $active]);
        $user->forceFill(['remember_token' => 'anterior'])->save();
        return $user;
    }

    public function test_normalizes_email_and_rejects_case_insensitive_duplicates(): void
    {
        $user = $this->account('  ANA@EXAMPLE.TEST  ');
        $this->assertSame('ana@example.test', $user->correo);
        $this->assertTrue(Validator::make(['correo' => 'ANA@example.test'], ['correo' => [new UniqueUserEmail]])->fails());
        $this->assertFalse(Validator::make(['correo' => 'ANA@example.test'], ['correo' => [new UniqueUserEmail($user->id_usuario)]])->fails());
        // Simula una cuenta histórica anterior a la normalización.
        DB::table('usuario')->where('id_usuario', $user->id_usuario)->update(['correo' => 'ANA@EXAMPLE.TEST']);
        $this->post(route('login.store'), ['correo' => 'ana@example.test', 'password' => 'AnteriorClave2026'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_is_neutral_and_excludes_inactive_accounts(): void
    {
        Notification::fake();
        $active = $this->account();
        $inactive = $this->account('inactiva@example.test', false);
        $response = $this->from(route('password.request'))->post(route('password.email'), ['correo' => 'ROOT@EXAMPLE.TEST']);
        $response->assertRedirect(route('password.request'))->assertSessionHas('status');
        $message = session('status');
        $this->post(route('password.email'), ['correo' => $inactive->correo])->assertSessionHas('status', $message);
        $this->post(route('password.email'), ['correo' => 'desconocida@example.test'])->assertSessionHas('status', $message);
        Notification::assertSentTo($active, ResetAccountPassword::class);
        Notification::assertNotSentTo($inactive, ResetAccountPassword::class);
        $this->assertFalse($inactive->fresh()->estado);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $inactive->correo]);
        $this->get(route('password.request'))->assertOk()->assertSee('Recuperar contraseña');
    }

    public function test_reset_token_is_single_use_and_rotates_remember_token(): void
    {
        Notification::fake();
        $user = $this->account();
        $token = Password::createToken($user);
        $this->get(route('password.reset', ['token' => $token, 'email' => $user->correo]))
            ->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $form = ['correo' => 'ROOT@EXAMPLE.TEST', 'token' => $token,
            'password' => 'Mi nueva frase Segura 2026', 'password_confirmation' => 'Mi nueva frase Segura 2026'];
        $this->post(route('password.update'), $form)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($form['password'], $user->fresh()->password_hash));
        $this->assertNotSame('anterior', $user->fresh()->remember_token);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->correo]);
        Notification::assertSentTo($user, PasswordChanged::class);
        $this->post(route('password.update'), $form)->assertSessionHasErrors('correo');
    }

    public function test_expired_tokens_and_disabled_accounts_cannot_reset(): void
    {
        $user = $this->account();
        $token = Password::createToken($user);
        $form = ['correo' => $user->correo, 'token' => $token, 'password' => 'ClaveNuevaSegura2026', 'password_confirmation' => 'ClaveNuevaSegura2026'];
        $this->travel(61)->minutes();
        $this->post(route('password.update'), $form)->assertSessionHasErrors('correo');
        $this->travelBack();
        $user->update(['estado' => false]);
        $this->post(route('password.update'), $form)->assertSessionHasErrors('correo');
        $this->assertTrue(Hash::check('AnteriorClave2026', $user->fresh()->password_hash));
        $this->assertFalse($user->fresh()->estado);
    }

    public function test_failed_invitation_resend_preserves_previous_link_and_records_failure(): void
    {
        $this->actingAs($this->account());
        $company = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Empresa', 'nombre_comercial' => 'Empresa', 'estado' => true]);
        $user = $this->account('invitada@example.test', false);
        $user->update(['id_empresa' => $company->id_empresa]);
        $hash = hash('sha256', 'enlace-anterior');
        $s = SolicitudAcceso::create(['ruc' => $company->ruc, 'razon_social' => 'Empresa', 'nombre_comercial' => 'Empresa', 'tipo_empresa' => 'Productora',
            'nombre' => 'Ana', 'apellido' => 'Perez', 'correo' => $user->correo, 'telefono' => '999888777', 'uso' => 'Control de calidad de lotes',
            'estado' => 'APROBADA', 'id_empresa' => $company->id_empresa, 'id_usuario' => $user->id_usuario,
            'invitacion_hash' => $hash, 'invitacion_expira_en' => now()->addDay(), 'invitacion_enviada_en' => now(), 'historial' => []]);
        $expiry = $s->invitacion_expira_en->toIso8601String();
        $service = \Mockery::mock(AccesoEmpresarial::class)->makePartial();
        $service->shouldReceive('correoDisponible')->andReturn(true);
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP no disponible'));
        $this->assertFalse($service->invitar($s->id));
        $this->assertSame($hash, $s->fresh()->invitacion_hash);
        $this->assertSame($expiry, $s->fresh()->invitacion_expira_en->toIso8601String());
        $this->assertNotNull($s->fresh()->invitacion_enviada_en);
        $this->assertSame('INVITACION_ENVIO_FALLIDO', collect($s->fresh()->historial)->last()['evento']);

        Mail::shouldReceive('send')->once()->andReturn(null);
        $this->assertTrue($service->invitar($s->id));
        $this->assertNotSame($hash, $s->fresh()->invitacion_hash);
        $this->assertGreaterThan($expiry, $s->fresh()->invitacion_expira_en->toIso8601String());
        $this->assertNotNull($s->fresh()->invitacion_enviada_en);
        $this->assertSame('INVITACION_ENVIADA', collect($s->fresh()->historial)->last()['evento']);
        $this->assertFalse($user->fresh()->estado);
    }
}
