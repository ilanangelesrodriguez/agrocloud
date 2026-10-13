<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maestro_valores', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 40);
            $table->foreignId('id_empresa')->nullable()->constrained('empresa', 'id_empresa');
            $table->foreignId('parent_id')->nullable()->constrained('maestro_valores')->restrictOnDelete();
            $table->string('nombre', 200);
            $table->string('codigo', 40)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->string('clave', 64)->unique();
            $table->timestamps();
            $table->index(['tipo', 'id_empresa', 'estado']);
        });
        Schema::create('maestro_empresa_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresa', 'id_empresa');
            $table->foreignId('maestro_id')->constrained('maestro_valores')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->unique(['id_empresa', 'maestro_id']);
        });
        Schema::table('dictamen_calidad', fn (Blueprint $table) => $table->string('motivo', 200)->nullable());
        Schema::table('lote', fn (Blueprint $table) => $table->string('unidad_medida', 40)->default('Kilogramo'));

        $insert = function ($tipo, $nombre, $company = null, $parent = null, $code = null) {
            $key = hash('sha256', $tipo.'|'.($company ?? 'global').'|'.($parent ?? '').'|'.mb_strtolower(trim($nombre)));
            DB::table('maestro_valores')->insertOrIgnore(['tipo' => $tipo, 'nombre' => trim($nombre), 'id_empresa' => $company, 'parent_id' => $parent, 'codigo' => $code, 'clave' => $key, 'estado' => true, 'created_at' => now(), 'updated_at' => now()]);
            return DB::table('maestro_valores')->where('clave', $key)->value('id');
        };
        foreach ([
            'cargo' => ['Administrador', 'Responsable de recepción', 'Inspector', 'Supervisor', 'Responsable de producción'],
            'tipo_empresa' => ['Productora', 'Exportadora', 'Comercializadora', 'Empacadora'],
            'rubro' => ['Producción agrícola', 'Exportación agrícola', 'Comercialización agrícola', 'Servicios de empaque'],
            'producto' => ['Palta', 'Arándano', 'Uva', 'Mango'],
            'metodo' => ['Aleatorio simple', 'Sistemático', 'Estratificado'],
            'defecto' => ['Daño mecánico', 'Daño por plagas', 'Enfermedad visible', 'Madurez fuera de rango'],
        ] as $kind => $names) foreach ($names as $name) $insert($kind, $name);
        foreach (array_keys(config('agrocloud.cargos', [])) as $name) $insert('cargo', $name);
        foreach (['ALEATORIO', 'SISTEMATICO', 'DIRIGIDO', 'OTRO'] as $name) $insert('metodo', $name);
        foreach (['Kilogramo' => 'kg', 'Gramo' => 'g', 'Tonelada' => 't'] as $name => $code) $insert('unidad', $name, null, null, $code);
        foreach (['Palta' => ['Hass', 'Fuerte'], 'Uva' => ['Red Globe'], 'Mango' => ['Kent', 'Haden']] as $product => $varieties) {
            $parent = $insert('producto', $product);
            foreach ($varieties as $name) $insert('variedad', $name, null, $parent);
        }
        // Conserva las opciones usadas actualmente, sin reemplazar sus nombres históricos.
        foreach (config('maestros') as $type => $definition) {
            if (in_array($type, ['ubigeo', 'variedad', 'unidad'])) continue;
            foreach ($definition['fields'] ?? [] as $table => $field) {
                foreach (DB::table($table)->whereNotNull($field)->where($field, '!=', '')->get([$field, 'id_empresa']) as $row) {
                    $company = in_array($definition['scope'], ['company', 'shared']) ? $row->id_empresa : null;
                    $insert($type, $row->$field, $company);
                }
            }
        }
        foreach (DB::table('lote')->whereNotNull('variedad')->where('variedad', '!=', '')->get(['producto', 'variedad']) as $row) {
            $insert('variedad', $row->variedad, null, $insert('producto', $row->producto));
        }
    }

    public function down(): void
    {
        Schema::table('lote', fn (Blueprint $table) => $table->dropColumn('unidad_medida'));
        Schema::table('dictamen_calidad', fn (Blueprint $table) => $table->dropColumn('motivo'));
        Schema::dropIfExists('maestro_empresa_config');
        Schema::dropIfExists('maestro_valores');
    }
};
