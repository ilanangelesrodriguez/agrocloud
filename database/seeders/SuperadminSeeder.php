<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('agrocloud.bootstrap_superadmin.email');
        $existing = User::query()->where('correo', $email)->first();
        if ($existing) {
            if (! $existing->isSuperAdmin() || $existing->id_empresa !== null) {
                throw new RuntimeException('El correo inicial ya pertenece a otra cuenta. No se modificó.');
            }
            // Conservar la contraseña y el estado de cuentas existentes.
            return;
        }
        User::query()->create([
            'correo' => $email,
            'password_hash' => config('agrocloud.bootstrap_superadmin.password'),
            'id_rol' => Rol::query()->where('codigo', 'SUPERADMIN')->firstOrFail()->id_rol,
            'id_empresa' => null,
            'nombre' => 'Superadmin',
            'apellido' => 'AgroCloud',
            'cargo' => 'Administración global',
            'estado' => true,
        ]);
    }
}
