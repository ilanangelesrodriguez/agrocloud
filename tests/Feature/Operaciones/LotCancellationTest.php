<?php

namespace Tests\Feature\Operaciones;

use App\Models\{Empresa, Lote, Permiso, Recepcion, Rol, User};
use App\Services\AnularLote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LotCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function setupLot(): array
    {
        $company = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Empresa', 'nombre_comercial' => 'Empresa', 'estado' => true]);
        $role = Rol::create(['codigo' => 'RECEPCION', 'nombre' => 'Recepción', 'estado' => true]);
        foreach (['lotes.eliminar', 'recepciones.crear'] as $code) {
            $p = Permiso::create(['codigo' => $code, 'nombre' => $code, 'modulo' => 'LOTES', 'estado' => true]);
            $role->permisos()->attach($p->id_permiso);
        }
        $user = User::create(['id_empresa' => $company->id_empresa, 'id_rol' => $role->id_rol, 'nombre' => 'Ana', 'correo' => 'ana@example.test', 'password_hash' => 'ClaveSegura2026', 'estado' => true]);
        $this->actingAs($user);
        $lot = Lote::create(['id_empresa' => $company->id_empresa, 'codigo_lote' => 'L-001', 'producto' => 'Palta', 'peso_kg' => 100, 'fecha_recepcion' => now(), 'estado' => 'PENDIENTE_RECEPCION']);
        return [$lot, $user];
    }

    public function test_cancellation_preserves_lot_and_trace_and_prevents_reception(): void
    {
        [$lot] = $this->setupLot();
        $events = DB::table('eventos_trazabilidad')->where('id_lote', $lot->id_lote)->count();
        $this->delete(route('lotes.destroy', $lot->id_lote))->assertRedirect(route('lotes.index'));
        $this->assertDatabaseHas('lote', ['id_lote' => $lot->id_lote, 'estado' => 'ANULADO']);
        $this->assertGreaterThan($events, DB::table('eventos_trazabilidad')->where('id_lote', $lot->id_lote)->count());
        $this->assertDatabaseHas('eventos_trazabilidad', ['id_lote' => $lot->id_lote, 'evento' => 'ANULADO']);
        $this->postJson(route('recepciones.store', $lot->id_lote), ['documento' => 'G-001', 'peso_recibido_kg' => 100, 'recibido_en' => now()->subMinute()->format('Y-m-d H:i:s')])->assertUnprocessable();
        $this->assertDatabaseCount('recepciones', 0);
    }

    public function test_received_lots_cannot_be_cancelled_or_deleted(): void
    {
        [$lot, $user] = $this->setupLot();
        Recepcion::create(['id_empresa' => $lot->id_empresa, 'id_lote' => $lot->id_lote, 'id_usuario' => $user->id_usuario,
            'documento' => 'G-001', 'peso_recibido_kg' => 100, 'recibido_en' => now()]);
        $this->deleteJson(route('lotes.destroy', $lot->id_lote))->assertUnprocessable()->assertJsonValidationErrors('lote');
        $this->assertDatabaseHas('lote', ['id_lote' => $lot->id_lote, 'estado' => 'PENDIENTE_RECEPCION']);
        $this->assertDatabaseCount('recepciones', 1);
    }

    public function test_cancellation_cannot_access_a_foreign_company_lot(): void
    {
        [$lot] = $this->setupLot();
        $foreign = Empresa::create(['ruc' => '20987654321', 'razon_social' => 'Otra', 'nombre_comercial' => 'Otra', 'estado' => true]);
        $lot->update(['id_empresa' => $foreign->id_empresa]);
        $this->deleteJson(route('lotes.destroy', $lot->id_lote))->assertNotFound();
        $this->assertDatabaseHas('lote', ['id_lote' => $lot->id_lote, 'estado' => 'PENDIENTE_RECEPCION']);
    }
}
