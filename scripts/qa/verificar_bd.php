<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\DatabaseSafety;

// Verificar antes de cualquier migración. Nunca imprimir la URL ni errores del proveedor.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
try {
    $expected = DatabaseSafety::verify(config('app.env'), config('database.default'), config('database.connections.pgsql.url'));
    $actual = DB::selectOne('select current_database() as database, current_user as username');
    if ($actual->database !== $expected['database'] || $actual->username !== $expected['user']) {
        throw new RuntimeException('Identidad inesperada.');
    }
    echo "Neon testing: conexión e identidad verificadas.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "Verificación de Neon testing fallida. No ejecutar migraciones.\n");
    exit(1);
}
