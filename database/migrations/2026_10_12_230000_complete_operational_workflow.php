<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $t->foreignId('id_lote')->unique()->constrained('lote', 'id_lote');
            $t->foreignId('id_usuario')->constrained('usuario', 'id_usuario');
            $t->string('documento', 100);
            $t->string('transportista', 150)->nullable();
            $t->string('placa', 20)->nullable();
            $t->decimal('peso_recibido_kg', 10, 2);
            $t->text('observaciones')->nullable();
            $t->timestamp('recibido_en');
            $t->timestamps();
            $t->index(['id_empresa', 'recibido_en']);
        });
        Schema::create('frutos_muestra', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $t->foreignId('id_muestreo')->constrained('muestreo', 'id_muestreo');
            $t->unsignedInteger('numero');
            $t->string('codigo', 60)->unique();
            $t->timestamps();
            $t->unique(['id_muestreo', 'numero']);
        });
        Schema::table('evidencia_inspeccion', fn (Blueprint $t) => $t->foreignId('id_fruto')->nullable()->constrained('frutos_muestra'));
        Schema::create('eventos_trazabilidad', function (Blueprint $t) {
            $t->id();
            $t->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $t->foreignId('id_lote')->constrained('lote', 'id_lote');
            $t->foreignId('id_usuario')->nullable()->constrained('usuario', 'id_usuario');
            $t->string('entidad', 80);
            $t->unsignedBigInteger('registro');
            $t->string('evento', 40);
            $t->json('cambios');
            $t->timestamp('created_at');
            $t->index(['id_empresa', 'id_lote', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_trazabilidad');
        Schema::table('evidencia_inspeccion', fn (Blueprint $t) => $t->dropConstrainedForeignId('id_fruto'));
        Schema::dropIfExists('frutos_muestra');
        Schema::dropIfExists('recepciones');
    }
};
