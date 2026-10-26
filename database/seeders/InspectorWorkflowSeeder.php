<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Aditivo e idempotente: no reasigna IDs, no modifica usuarios existentes,
 * y no sustituye las contraseñas de cuentas actuales.
 */
class InspectorWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            ['muestreos.ver', 'Ver muestreos', 'MUESTREOS'],
            ['muestreos.crear', 'Crear muestreos', 'MUESTREOS'],
            ['inspecciones.ver', 'Ver inspecciones', 'INSPECCIONES'],
            ['inspecciones.crear', 'Iniciar inspecciones', 'INSPECCIONES'],
            ['inspecciones.capturar', 'Cargar evidencias', 'INSPECCIONES'],
            ['inspecciones.analizar', 'Solicitar análisis IA', 'INSPECCIONES'],
            ['inspecciones.validar', 'Validar detecciones', 'INSPECCIONES'],
            ['calidad.ver', 'Ver dictámenes de calidad', 'CALIDAD'],
            ['calidad.clasificar', 'Emitir dictamen de calidad', 'CALIDAD'],
        ];

        $ids = [];
        foreach ($definitions as [$codigo, $nombre, $modulo]) {
            $permiso = Permiso::query()->updateOrCreate(
                ['codigo' => $codigo],
                ['nombre' => $nombre, 'modulo' => $modulo, 'descripcion' => $nombre.' en AgroCloud AI.', 'estado' => true]
            );
            $ids[$codigo] = $permiso->id_permiso;
        }

        $matrix = [
            'SUPERADMIN' => array_keys($ids),
            'ADMIN' => ['muestreos.ver', 'inspecciones.ver', 'calidad.ver'],
            'RECEPCION' => [],
            'INSPECTOR' => [
                'muestreos.ver', 'muestreos.crear', 'inspecciones.ver', 'inspecciones.crear',
                'inspecciones.capturar', 'inspecciones.analizar', 'inspecciones.validar',
            ],
            'SUPERVISOR' => ['muestreos.ver', 'inspecciones.ver', 'calidad.ver', 'calidad.clasificar'],
            'PRODUCCION' => [],
        ];

        foreach ($matrix as $code => $permissions) {
            $rol = Rol::query()->where('codigo', $code)->firstOrFail();
            $rol->permisos()->syncWithoutDetaching(array_map(fn ($p) => $ids[$p], $permissions));
        }

        // El inspector debe poder leer el lote relacionado, pero nunca crearlo o editarlo.
        $lotesVer = Permiso::query()->where('codigo', 'lotes.ver')->value('id_permiso');
        if ($lotesVer) {
            foreach (['INSPECTOR', 'SUPERVISOR'] as $code) {
                Rol::query()->where('codigo', $code)->firstOrFail()
                    ->permisos()->syncWithoutDetaching([$lotesVer]);
            }
        }

    }
}
