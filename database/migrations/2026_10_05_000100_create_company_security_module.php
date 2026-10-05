<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createOrExtendEmpresa();
        $this->createOrExtendRol();
        $this->createOrExtendUsuario();
        $this->createPermiso();
        $this->createRolPermiso();
    }

    public function down(): void
    {
        // Rollback deliberadamente conservador: no elimina empresa, rol o usuario
        // porque esas tablas pueden haber existido antes de esta migración.
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permiso');
    }

    private function createOrExtendEmpresa(): void
    {
        if (! Schema::hasTable('empresa')) {
            Schema::create('empresa', function (Blueprint $table) {
                $table->bigIncrements('id_empresa');
                $table->string('ruc', 11)->unique();
                $table->string('razon_social', 200);
                $table->string('nombre_comercial', 150);
                $table->string('grupo_empresarial', 150)->nullable();
                $table->string('tipo_empresa', 80)->nullable();
                $table->string('rubro', 150)->nullable();
                $table->text('descripcion')->nullable();
                $table->string('direccion', 250)->nullable();
                $table->string('ubigeo', 6)->nullable();
                $table->string('departamento', 100)->nullable();
                $table->string('provincia', 100)->nullable();
                $table->string('distrito', 100)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('correo')->nullable();
                $table->string('sitio_web')->nullable();
                $table->text('logo_url')->nullable();
                $table->string('logo_public_id', 255)->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });

            return;
        }

        $columns = [
            'grupo_empresarial' => fn (Blueprint $table) => $table->string('grupo_empresarial', 150)->nullable(),
            'tipo_empresa' => fn (Blueprint $table) => $table->string('tipo_empresa', 80)->nullable(),
            'rubro' => fn (Blueprint $table) => $table->string('rubro', 150)->nullable(),
            'descripcion' => fn (Blueprint $table) => $table->text('descripcion')->nullable(),
            'ubigeo' => fn (Blueprint $table) => $table->string('ubigeo', 6)->nullable(),
            'departamento' => fn (Blueprint $table) => $table->string('departamento', 100)->nullable(),
            'provincia' => fn (Blueprint $table) => $table->string('provincia', 100)->nullable(),
            'distrito' => fn (Blueprint $table) => $table->string('distrito', 100)->nullable(),
            'telefono' => fn (Blueprint $table) => $table->string('telefono', 30)->nullable(),
            'correo' => fn (Blueprint $table) => $table->string('correo')->nullable(),
            'sitio_web' => fn (Blueprint $table) => $table->string('sitio_web')->nullable(),
            'logo_url' => fn (Blueprint $table) => $table->text('logo_url')->nullable(),
            'logo_public_id' => fn (Blueprint $table) => $table->string('logo_public_id', 255)->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('empresa', $column)) {
                Schema::table('empresa', $callback);
            }
        }
    }

    private function createOrExtendRol(): void
    {
        if (! Schema::hasTable('rol')) {
            Schema::create('rol', function (Blueprint $table) {
                $table->bigIncrements('id_rol');
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamps();
            });

            return;
        }

        foreach ([
            'codigo' => fn (Blueprint $table) => $table->string('codigo', 50)->nullable(),
            'descripcion' => fn (Blueprint $table) => $table->text('descripcion')->nullable(),
            'estado' => fn (Blueprint $table) => $table->boolean('estado')->default(true),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('rol', $column)) {
                Schema::table('rol', $callback);
            }
        }
    }

    private function createOrExtendUsuario(): void
    {
        if (! Schema::hasTable('usuario')) {
            Schema::create('usuario', function (Blueprint $table) {
                $table->bigIncrements('id_usuario');
                $table->unsignedBigInteger('id_empresa')->nullable();
                $table->unsignedBigInteger('id_rol')->nullable();
                $table->string('nombre', 150);
                $table->string('apellido', 150)->nullable();
                $table->string('correo')->unique();
                $table->string('password_hash');
                $table->string('telefono', 30)->nullable();
                $table->string('cargo', 120)->nullable();
                $table->text('foto_perfil_url')->nullable();
                $table->string('foto_perfil_public_id', 255)->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamp('ultimo_acceso')->nullable();
                $table->timestamps();

                $table->foreign('id_empresa')->references('id_empresa')->on('empresa')->nullOnDelete();
                $table->foreign('id_rol')->references('id_rol')->on('rol')->nullOnDelete();
            });

            return;
        }

        foreach ([
            'apellido' => fn (Blueprint $table) => $table->string('apellido', 150)->nullable(),
            'telefono' => fn (Blueprint $table) => $table->string('telefono', 30)->nullable(),
            'cargo' => fn (Blueprint $table) => $table->string('cargo', 120)->nullable(),
            'foto_perfil_url' => fn (Blueprint $table) => $table->text('foto_perfil_url')->nullable(),
            'foto_perfil_public_id' => fn (Blueprint $table) => $table->string('foto_perfil_public_id', 255)->nullable(),
        ] as $column => $callback) {
            if (! Schema::hasColumn('usuario', $column)) {
                Schema::table('usuario', $callback);
            }
        }
    }

    private function createPermiso(): void
    {
        if (Schema::hasTable('permiso')) {
            return;
        }

        Schema::create('permiso', function (Blueprint $table) {
            $table->bigIncrements('id_permiso');
            $table->string('codigo', 120)->unique();
            $table->string('nombre', 150);
            $table->string('modulo', 80)->index();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    private function createRolPermiso(): void
    {
        if (Schema::hasTable('rol_permiso')) {
            return;
        }

        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');
            $table->primary(['id_rol', 'id_permiso']);
            $table->foreign('id_rol')->references('id_rol')->on('rol')->cascadeOnDelete();
            $table->foreign('id_permiso')->references('id_permiso')->on('permiso')->cascadeOnDelete();
        });
    }
};
