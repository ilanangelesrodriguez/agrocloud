<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitudes_acceso', function (Blueprint $table) {
            $table->id();
            $table->string('ruc', 11)->unique();
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 150);
            $table->string('tipo_empresa', 80);
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 254);
            $table->string('telefono', 30);
            $table->text('uso');
            $table->string('estado', 20)->default('PENDIENTE')->index();
            $table->foreignId('id_empresa')->nullable()->constrained('empresa', 'id_empresa')->restrictOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('usuario', 'id_usuario')->restrictOnDelete();
            $table->foreignId('revisada_por')->nullable()->constrained('usuario', 'id_usuario')->nullOnDelete();
            $table->text('motivo')->nullable();
            $table->timestamp('decidida_en')->nullable();
            $table->json('historial');
            $table->string('invitacion_hash', 64)->nullable();
            $table->timestamp('invitacion_expira_en')->nullable();
            $table->timestamp('invitacion_enviada_en')->nullable();
            $table->timestamp('activada_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_acceso');
    }
};
