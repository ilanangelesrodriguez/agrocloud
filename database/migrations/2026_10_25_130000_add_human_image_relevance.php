<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evidencia_inspeccion', function (Blueprint $table) {
            $table->string('pertinencia', 20)->default('PENDIENTE');
            $table->text('motivo_descarte')->nullable();
        });
        // Preserve closed historical dossiers. Open ones require explicit human confirmation.
        DB::table('evidencia_inspeccion')->whereIn('id_inspeccion', DB::table('inspeccion')->where('estado', 'VALIDADA')->select('id_inspeccion'))->update(['pertinencia' => 'VALIDA']);
    }

    public function down(): void
    {
        Schema::table('evidencia_inspeccion', fn (Blueprint $table) => $table->dropColumn(['pertinencia', 'motivo_descarte']));
    }
};
