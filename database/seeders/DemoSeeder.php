<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/** Datos de demostración: ejecutar únicamente de forma explícita. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FoundationSeeder::class);
        // ECONOAGRO es la primera empresa, no una empresa codificada
        // como única del sistema.
        $empresa = Empresa::query()->updateOrCreate(
            ['ruc' => '20550844321'],
            [
                'razon_social' => 'AGROPUNTO SOCIEDAD ANONIMA CERRADA - AGROPUNTO S.A.C.',
                'nombre_comercial' => 'ECONOAGRO',
                'grupo_empresarial' => 'ARIS',
                'tipo_empresa' => 'AGROQUIMICOS',
                'rubro' => 'Agroquímicos y soluciones agrícolas',
                'estado' => true,
            ],
        );

        $roles = [
            'ADMIN' => ['admin.demo@agrocloud.test', 'Administrador', 'Administración'],
            'RECEPCION' => ['recepcion.demo@agrocloud.test', 'Recepción', 'Recepción'],
            'INSPECTOR' => ['inspector.demo@agrocloud.test', 'Inspector', 'Inspección'],
            'SUPERVISOR' => ['supervisor.demo@agrocloud.test', 'Supervisor', 'Supervisión'],
            'PRODUCCION' => ['produccion.demo@agrocloud.test', 'Producción', 'Producción'],
        ];

        $credentials = [];
        foreach ($roles as $code => [$email, $nombre, $cargo]) {
            $role = Rol::query()->where('codigo', $code)->firstOrFail();
            $query = User::query()->where('id_rol', $role->id_rol);
            $query->where('id_empresa', $empresa->id_empresa);
            if ($query->exists()) {
                $this->command?->line("{$code}: cuenta existente conservada.");
                continue;
            }
            if (User::query()->where('correo', $email)->exists()) {
                throw new RuntimeException("El correo {$email} ya existe con otro rol o empresa. Revísalo antes de continuar.");
            }

            $password = Str::password(22);
            User::query()->create([
                'id_empresa' => $empresa->id_empresa,
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
            $this->command?->info('Los roles empresariales ya cuentan con al menos una cuenta en el contexto correspondiente.');
        }
    }
}
