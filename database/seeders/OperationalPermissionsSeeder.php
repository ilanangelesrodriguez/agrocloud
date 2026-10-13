<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class OperationalPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['recepciones.ver' => 'Consultar recepciones', 'recepciones.crear' => 'Confirmar recepción', 'trazabilidad.ver' => 'Consultar trazabilidad', 'reportes.operativos' => 'Consultar reportes operativos'] as $code => $name) {
            Permiso::firstOrCreate(['codigo' => $code], ['nombre' => $name, 'modulo' => 'OPERACIONES', 'estado' => true]);
        }
        foreach (['ADMIN', 'RECEPCION', 'INSPECTOR', 'SUPERVISOR', 'PRODUCCION'] as $code) {
            $role = Rol::where('codigo', $code)->first();
            if (! $role) {
                continue;
            }
            $permissions = ['recepciones.ver', 'trazabilidad.ver', 'reportes.operativos', 'lotes.ver'];
            if ($code === 'RECEPCION') {
                $permissions[] = 'recepciones.crear';
            }
            if ($code === 'PRODUCCION') {
                array_push($permissions, 'muestreos.ver', 'inspecciones.ver', 'calidad.ver');
            }
            $role->permisos()->syncWithoutDetaching(Permiso::whereIn('codigo', $permissions)->pluck('id_permiso')->all());
        }
    }
}
