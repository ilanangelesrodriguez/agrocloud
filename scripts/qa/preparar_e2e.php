<?php

// Solo se invoca en CI, después de verificar la identidad real de Neon testing.
require __DIR__.'/verificar_bd.php';

use App\Models\{Empresa, Lote, MaestroValor, Permiso, Productor, Rol, User};
use Illuminate\Support\Facades\DB;

$password = getenv('QA_E2E_PASSWORD');
if (!is_string($password) || strlen($password) < 24) {
    throw new RuntimeException('Falta una contraseña temporal de QA.');
}

DB::transaction(function () use ($password) {
    MaestroValor::firstOrCreate(['tipo' => 'tipo_empresa', 'nombre' => 'Productora ficticia QA', 'id_empresa' => null], ['estado' => true, 'clave' => hash('sha256', 'qa-e2e:tipo_empresa:productora')]);
    $permissions = ['dashboard.ver', 'empresa.ver', 'empresa.editar', 'usuarios.ver', 'productores.ver', 'productores.crear', 'productores.editar', 'lotes.ver', 'lotes.crear', 'recepciones.ver', 'trazabilidad.ver', 'reportes.operativos'];
    $role = Rol::updateOrCreate(['codigo' => 'QA_E2E_ADMIN'], ['nombre' => 'Administrador ficticio QA', 'estado' => true]);
    foreach ($permissions as $code) {
        $permission = Permiso::firstOrCreate(['codigo' => $code], ['nombre' => $code, 'modulo' => 'QA', 'estado' => true]);
        $role->permisos()->syncWithoutDetaching([$permission->id_permiso]);
    }
    $super = Rol::updateOrCreate(['codigo' => 'SUPERADMIN'], ['nombre' => 'Superadministrador QA', 'estado' => true]);
    $company = Empresa::updateOrCreate(['ruc' => '20999999001'], ['razon_social' => 'EMPRESA FICTICIA QA UNO SAC', 'nombre_comercial' => 'Empresa ficticia QA Uno', 'estado' => true]);
    $other = Empresa::updateOrCreate(['ruc' => '20999999002'], ['razon_social' => 'EMPRESA FICTICIA QA DOS SAC', 'nombre_comercial' => 'Empresa ficticia QA Dos', 'estado' => true]);
    foreach ([['qa-admin@example.test', $company->id_empresa, $role->id_rol], ['qa-root@example.test', null, $super->id_rol]] as [$email, $tenant, $roleId]) {
        User::updateOrCreate(['correo' => $email], ['id_empresa' => $tenant, 'id_rol' => $roleId, 'nombre' => 'Usuario ficticio QA', 'apellido' => 'Demostración', 'password_hash' => $password, 'estado' => true]);
    }
    $producer = Productor::updateOrCreate(['id_empresa' => $company->id_empresa, 'ruc' => '10999999001'], ['nombre_razon_social' => 'Productor ficticio QA Uno', 'fundo' => 'Fundo demostración']);
    $foreign = Productor::updateOrCreate(['id_empresa' => $other->id_empresa, 'ruc' => '10999999002'], ['nombre_razon_social' => 'Productor reservado QA Dos']);
    // Crear sin observadores: las fixtures no deben producir alertas ni auditorías operativas.
    Lote::withoutEvents(fn () => Lote::updateOrCreate(['codigo_lote' => 'QA-E2E-LOTE-001', 'id_empresa' => $company->id_empresa], ['id_productor' => $producer->id_productor, 'producto' => 'Palta', 'peso_kg' => 100, 'fecha_recepcion' => '2026-01-15 10:00:00', 'estado' => 'PENDIENTE_RECEPCION']));
    if (!is_dir(base_path('playwright/.auth'))) mkdir(base_path('playwright/.auth'), 0700, true);
    file_put_contents(base_path('playwright/.auth/fixtures.json'), json_encode(['other_producer' => $foreign->id_productor], JSON_THROW_ON_ERROR));
});
echo "Fixtures ficticias QA listas; no se han eliminado registros.\n";
