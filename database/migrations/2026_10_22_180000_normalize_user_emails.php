<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // No fusionar cuentas ni reasignar su empresa en caso de colisión.
        DB::statement('LOCK TABLE usuario IN SHARE ROW EXCLUSIVE MODE');
        if (DB::table('usuario')->selectRaw('LOWER(TRIM(correo))')->groupByRaw('LOWER(TRIM(correo))')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Hay correos de usuario duplicados al normalizar. Resuelve las cuentas en conflicto antes de migrar.');
        }
        DB::statement('UPDATE usuario SET correo = LOWER(TRIM(correo))');
        DB::statement('CREATE UNIQUE INDEX usuario_correo_normalizado_unique ON usuario (LOWER(TRIM(correo)))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS usuario_correo_normalizado_unique');
    }
};
