<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // PostgreSQL holds this lock until the migration transaction commits,
        // preventing inserts between the emptiness check and the drop.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('LOCK TABLE "users" IN ACCESS EXCLUSIVE MODE');
        }

        if (DB::table('users')->exists()) {
            throw new RuntimeException('No se eliminó users: contiene datos que deben conservarse.');
        }

        // Refuse to remove a table referenced by another table. Never cascade.
        foreach (Schema::getTables() as $table) {
            foreach (Schema::getForeignKeys($table['name']) as $foreignKey) {
                if ($foreignKey['foreign_table'] === 'users') {
                    throw new RuntimeException('No se eliminó users: tiene claves foráneas dependientes.');
                }
            }
        }

        Schema::drop('users');
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
};
