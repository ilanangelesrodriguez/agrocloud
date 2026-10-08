<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productor', function (Blueprint $table) {
            $table->bigIncrements('id_productor');

            $table->unsignedBigInteger('id_empresa');

            $table->string('ruc', 11);
            $table->string('nombre_razon_social', 200);
            $table->string('fundo', 150)->nullable();
            $table->string('ubicacion', 250)->nullable();

            $table->timestamps();

            $table->unique(['id_empresa', 'ruc']);

            $table->foreign('id_empresa')
                ->references('id_empresa')
                ->on('empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productor');
    }
};
