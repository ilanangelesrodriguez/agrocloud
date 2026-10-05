<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class SecurityAndCompanySeeder extends Seeder
{
    public function run(): void
    {
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
            'ADMIN' => ['Administrador', 'Acceso total de administración y seguridad.'],
            'RECEPCION' => ['Responsable de recepción', 'Gestiona la recepción de lotes.'],
            'INSPECTOR' => ['Inspector de control de calidad', 'Realiza inspecciones, evidencias y validaciones.'],
            'SUPERVISOR' => ['Supervisor de Pre-Empaque', 'Supervisa el proceso de Pre-Empaque.'],
            'PRODUCCION' => ['Responsable de producción', 'Consulta y gestiona información de producción.'],
        ];

        foreach ($roles as $codigo => [$nombre, $descripcion]) {
            Rol::query()->updateOrCreate(
                ['codigo' => $codigo],
                ['nombre' => $nombre, 'descripcion' => $descripcion, 'estado' => true],
            );
        }

        $permissions = [
            ['dashboard.ver', 'Ver dashboard', 'DASHBOARD', 'Acceder al dashboard principal.'],
            ['empresa.ver', 'Ver empresa', 'EMPRESA', 'Consultar la empresa asignada.'],
            ['empresa.editar', 'Editar empresa', 'EMPRESA', 'Actualizar datos y logo de la empresa.'],
            ['usuarios.ver', 'Ver usuarios', 'USUARIOS', 'Listar usuarios de la empresa.'],
            ['usuarios.crear', 'Crear usuarios', 'USUARIOS', 'Crear cuentas dentro de la empresa.'],
            ['usuarios.editar', 'Editar usuarios', 'USUARIOS', 'Editar datos, rol, contraseña y foto.'],
            ['usuarios.estado', 'Cambiar estado de usuarios', 'USUARIOS', 'Activar o desactivar usuarios.'],
            ['roles.ver', 'Ver roles', 'SEGURIDAD', 'Consultar roles disponibles.'],
            ['roles.editar', 'Asignar permisos a roles', 'SEGURIDAD', 'Configurar la matriz de permisos por rol.'],
            ['permisos.ver', 'Ver permisos', 'SEGURIDAD', 'Consultar el catálogo de permisos.'],
            ['permisos.crear', 'Crear permisos', 'SEGURIDAD', 'Agregar permisos al catálogo.'],
            ['permisos.editar', 'Editar permisos', 'SEGURIDAD', 'Modificar permisos existentes.'],
            ['permisos.estado', 'Cambiar estado de permisos', 'SEGURIDAD', 'Activar o desactivar permisos.'],
        ];

        $permissionIds = [];
        foreach ($permissions as [$codigo, $nombre, $modulo, $descripcion]) {
            $permission = Permiso::query()->updateOrCreate(
                ['codigo' => $codigo],
                compact('nombre', 'modulo', 'descripcion') + ['estado' => true],
            );
            $permissionIds[$codigo] = $permission->id_permiso;
        }

        $admin = Rol::query()->where('codigo', 'ADMIN')->firstOrFail();
        $admin->permisos()->syncWithoutDetaching(array_values($permissionIds));

        $basic = array_values(array_filter([
            $permissionIds['dashboard.ver'] ?? null,
            $permissionIds['empresa.ver'] ?? null,
        ]));

        Rol::query()
            ->whereIn('codigo', ['RECEPCION', 'INSPECTOR', 'SUPERVISOR', 'PRODUCCION'])
            ->get()
            ->each(fn (Rol $rol) => $rol->permisos()->syncWithoutDetaching($basic));

        // En este proyecto actualmente se trabaja con una empresa principal.
        // Usuarios antiguos sin empresa quedan asociados a ECONOAGRO de forma no destructiva.
        User::query()->whereNull('id_empresa')->update(['id_empresa' => $empresa->id_empresa]);

        $adminEmail = config('agrocloud.bootstrap_admin.email');
        $adminPassword = config('agrocloud.bootstrap_admin.password');

        if ($adminEmail && $adminPassword) {
            User::query()->updateOrCreate(
                ['correo' => $adminEmail],
                [
                    'id_empresa' => $empresa->id_empresa,
                    'id_rol' => $admin->id_rol,
                    'nombre' => config('agrocloud.bootstrap_admin.name'),
                    'apellido' => config('agrocloud.bootstrap_admin.lastname'),
                    'password_hash' => $adminPassword,
                    'estado' => true,
                ],
            );
        }
    }
}
