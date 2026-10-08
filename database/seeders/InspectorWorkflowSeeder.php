<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

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

        $empresa = Empresa::query()->where('estado', true)->orderBy('id_empresa')->first();
        if (! $empresa) {
            throw new RuntimeException('Primero registra una empresa activa y ejecuta SecurityAndCompanySeeder.');
        }

        $roles = [
            'ADMIN' => ['admin.demo@agrocloud.test', 'Administrador', 'Administración'],
            'RECEPCION' => ['recepcion.demo@agrocloud.test', 'Recepción', 'Recepción'],
            'INSPECTOR' => ['inspector.demo@agrocloud.test', 'Inspector', 'Inspección'],
            'SUPERVISOR' => ['supervisor.demo@agrocloud.test', 'Supervisor', 'Supervisión'],
            'PRODUCCION' => ['produccion.demo@agrocloud.test', 'Producción', 'Producción'],
            'SUPERADMIN' => ['superadmin.demo@agrocloud.test', 'Superadmin', 'Plataforma'],
        ];

        $credentials = [];
        foreach ($roles as $code => [$email, $nombre, $cargo]) {
            $role = Rol::query()->where('codigo', $code)->firstOrFail();
            $query = User::query()->where('id_rol', $role->id_rol);
            if ($code === 'SUPERADMIN') {
                $query->whereNull('id_empresa');
            } else {
                $query->where('id_empresa', $empresa->id_empresa);
            }
            if ($query->exists()) {
                $this->command?->line("{$code}: cuenta existente conservada.");
                continue;
            }
            if (User::query()->where('correo', $email)->exists()) {
                throw new RuntimeException("El correo {$email} ya existe con otro rol o empresa. Revísalo antes de continuar.");
            }

            $password = Str::password(22);
            User::query()->create([
                'id_empresa' => $code === 'SUPERADMIN' ? null : $empresa->id_empresa,
                'id_rol' => $role->id_rol,
                'nombre' => $nombre,
                'apellido' => 'Demo',
                'correo' => $email,
                'cargo' => $cargo,
                'estado' => true,
                'password_hash' => $password,
            ]);
            $credentials[] = "{$code}\t{$email}\t{$password}";
        }

        if ($credentials) {
            $directory = storage_path('app/private');
            File::ensureDirectoryExists($directory);
            $path = $directory.'/agrocloud-seed-users-credentials.txt';
            $previous = is_file($path) ? (string) file_get_contents($path) : '';
            file_put_contents($path, $previous."\nCREADOS ".now()->toDateTimeString()."\n".implode("\n", $credentials)."\n", LOCK_EX);
            @chmod($path, 0600);
            $this->command?->warn('Credenciales nuevas guardadas en storage/app/private/agrocloud-seed-users-credentials.txt (no subir a Git).');
        } else {
            $this->command?->info('Los seis roles ya cuentan con al menos una cuenta en el contexto correspondiente.');
        }
    }
}
