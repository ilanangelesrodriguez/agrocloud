<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('invitation_hash', 64)->nullable();
            $table->timestamp('invitation_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('usuario', fn (Blueprint $table) => $table->dropColumn(['invitation_hash', 'invitation_expires_at']));
    }
};
