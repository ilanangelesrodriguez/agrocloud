<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productor', function (Blueprint $table) {
            $table->string('departamento', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('ubigeo', 6)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('productor', fn (Blueprint $table) => $table->dropColumn(['departamento', 'provincia', 'distrito', 'ubigeo']));
    }
};
