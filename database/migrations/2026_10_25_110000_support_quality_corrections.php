<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dictamen_calidad', function (Blueprint $table) {
            $table->dropUnique(['id_inspeccion']);
            $table->index('id_inspeccion');
            $table->string('tipo_observacion', 30)->nullable();
            $table->text('respuesta_observacion')->nullable();
            $table->unsignedBigInteger('reemplaza_dictamen')->nullable()->unique();
        });
        DB::table('dictamen_calidad')->where('decision', 'OBSERVADO')->update(['tipo_observacion' => 'REINSPECCION']);
    }

    public function down(): void
    {
        if (DB::table('dictamen_calidad')->groupBy('id_inspeccion')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new \RuntimeException('No se puede revertir: existen decisiones históricas sucesivas. Conserva los registros de calidad.');
        }
        Schema::table('dictamen_calidad', function (Blueprint $table) {
            $table->dropIndex(['id_inspeccion']);
            $table->dropUnique(['reemplaza_dictamen']);
            $table->dropColumn(['tipo_observacion', 'respuesta_observacion', 'reemplaza_dictamen']);
            $table->unique('id_inspeccion');
        });
    }
};
