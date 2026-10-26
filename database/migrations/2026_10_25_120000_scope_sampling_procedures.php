<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sampling_procedures', function (Blueprint $table) {
            $table->string('producto', 150)->nullable();
            $table->boolean('requiere_poblacion')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('sampling_procedures', fn (Blueprint $table) => $table->dropColumn(['producto', 'requiere_poblacion']));
    }
};
