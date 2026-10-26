<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('pending_email', 255)->nullable()->unique();
            $table->string('email_change_hash', 64)->nullable();
            $table->timestamp('email_change_expires_at')->nullable();
            $table->timestamp('invitation_attempted_at')->nullable();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->string('invitation_delivery_status', 30)->nullable();
            $table->unsignedInteger('auth_version')->default(0);
            $table->json('muted_operational_kinds')->nullable();
        });
        Schema::create('account_security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa')->nullable()->index();
            $table->unsignedBigInteger('id_usuario')->index();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event', 60);
            $table->json('changes');
            $table->timestamp('created_at');
        });
        Schema::create('security_mail_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->nullable()->index();
            $table->string('kind', 60);
            $table->string('status', 30)->default('queued');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });
        Schema::table('lote', fn (Blueprint $table) => $table->dateTime('fecha_llegada_prevista')->nullable());
        // Only unreceived lots retain an unambiguous planned date. Never invent
        // historical arrival estimates that were overwritten by a real reception.
        DB::table('lote')->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('recepciones')->whereColumn('recepciones.id_lote', 'lote.id_lote');
        })->update(['fecha_llegada_prevista' => DB::raw('fecha_recepcion')]);
        Schema::table('recepciones', fn (Blueprint $table) => $table->string('tipo_documento', 50)->nullable());
        Schema::create('sampling_procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa')->index();
            $table->string('nombre', 150);
            $table->string('version', 30);
            $table->string('metodo', 80);
            $table->text('criterio_muestra');
            $table->text('seleccion_frutos');
            $table->unsignedInteger('tamano_recomendado')->nullable();
            $table->unsignedInteger('max_frutos');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['id_empresa', 'nombre', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sampling_procedures');
        Schema::table('recepciones', fn (Blueprint $table) => $table->dropColumn('tipo_documento'));
        Schema::table('lote', fn (Blueprint $table) => $table->dropColumn('fecha_llegada_prevista'));
        Schema::dropIfExists('security_mail_attempts');
        Schema::dropIfExists('account_security_events');
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropUnique(['pending_email']);
            $table->dropColumn(['pending_email', 'email_change_hash', 'email_change_expires_at', 'invitation_attempted_at', 'invitation_sent_at', 'invitation_delivery_status', 'auth_version', 'muted_operational_kinds']);
        });
    }
};
