<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['empresa', 'productor', 'solicitudes_acceso'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->json('sunat_verificacion')->nullable());
        }
    }

    public function down(): void
    {
        foreach (['empresa', 'productor', 'solicitudes_acceso'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('sunat_verificacion'));
        }
    }
};
