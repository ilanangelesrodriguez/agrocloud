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
    if (Schema::hasTable('lote')) {
        return;
    }

    Schema::create('lote', function (Blueprint $table) {
        $table->bigIncrements('id_lote');
        $table->unsignedBigInteger('id_empresa');
        $table->unsignedBigInteger('id_productor')->nullable(); // sin FK: productor aún no existe
        $table->string('codigo_lote', 50);
        $table->string('producto', 150);
        $table->string('variedad', 150)->nullable();
        $table->decimal('peso_kg', 10, 2);
        $table->dateTime('fecha_recepcion');
        $table->string('estado', 30);
        $table->timestamps();

        $table->unique(['id_empresa', 'codigo_lote']);
        $table->foreign('id_empresa')->references('id_empresa')->on('empresa');
    });
}

public function down(): void
{
    Schema::dropIfExists('lote');
}
};
