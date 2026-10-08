<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muestreo', function (Blueprint $table) {
            $table->bigIncrements('id_muestreo');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('id_lote')->constrained('lote', 'id_lote');
            $table->foreignId('id_inspector')->constrained('usuario', 'id_usuario');
            $table->unsignedInteger('tamano_muestra');
            $table->string('metodo', 80);
            $table->text('observaciones')->nullable();
            $table->string('estado', 30)->default('REGISTRADO');
            $table->timestamps();
            $table->index(['id_empresa', 'id_lote']);
        });

        Schema::create('inspeccion', function (Blueprint $table) {
            $table->bigIncrements('id_inspeccion');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('id_muestreo')->constrained('muestreo', 'id_muestreo');
            $table->foreignId('id_inspector')->nullable()->constrained('usuario', 'id_usuario');
            $table->foreignId('solicitada_por')->nullable()->constrained('usuario', 'id_usuario');
            $table->string('estado', 35)->default('EN_CAPTURA');
            $table->text('observaciones')->nullable();
            $table->timestamp('validada_en')->nullable();
            $table->timestamps();
            $table->index(['id_empresa', 'estado']);
        });

        Schema::create('evidencia_inspeccion', function (Blueprint $table) {
            $table->bigIncrements('id_evidencia');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('id_inspeccion')->constrained('inspeccion', 'id_inspeccion');
            $table->text('imagen_url');
            $table->string('imagen_public_id', 255);
            $table->text('imagen_detectada_url')->nullable();
            $table->unsignedBigInteger('ia_upload_id')->nullable();
            $table->string('estado_ia', 30)->default('PENDIENTE');
            $table->string('version_modelo', 32)->nullable();
            $table->json('resultado_ia')->nullable();
            $table->text('error_ia')->nullable();
            $table->timestamps();
            $table->index(['id_empresa', 'id_inspeccion']);
        });

        Schema::create('validacion_deteccion', function (Blueprint $table) {
            $table->bigIncrements('id_validacion');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('id_evidencia')->constrained('evidencia_inspeccion', 'id_evidencia');
            $table->foreignId('id_inspector')->constrained('usuario', 'id_usuario');
            $table->unsignedInteger('indice_deteccion');
            $table->boolean('confirmada');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique(['id_evidencia', 'indice_deteccion']);
        });

        Schema::create('hallazgo_manual', function (Blueprint $table) {
            $table->bigIncrements('id_hallazgo');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('id_inspeccion')->constrained('inspeccion', 'id_inspeccion');
            $table->foreignId('id_evidencia')->nullable()->constrained('evidencia_inspeccion', 'id_evidencia');
            $table->foreignId('id_inspector')->constrained('usuario', 'id_usuario');
            $table->string('clase', 150);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('dictamen_calidad', function (Blueprint $table) {
            $table->bigIncrements('id_dictamen');
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('id_lote')->constrained('lote', 'id_lote');
            $table->foreignId('id_inspeccion')->constrained('inspeccion', 'id_inspeccion');
            $table->foreignId('id_supervisor')->constrained('usuario', 'id_usuario');
            $table->string('decision', 25);
            $table->string('categoria', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique('id_inspeccion');
            $table->index(['id_empresa', 'decision']);
        });
    }

    public function down(): void
    {
        foreach (['dictamen_calidad', 'hallazgo_manual', 'validacion_deteccion', 'evidencia_inspeccion', 'inspeccion', 'muestreo'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
