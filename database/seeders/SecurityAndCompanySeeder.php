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
        // ECONOAGRO es la primera empresa, no una empresa codificada como única del sistema.
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
            'SUPERADMIN' => ['Superadministrador AgroCloud', 'Rol reservado para administración global de la plataforma y futuras empresas.'],
            'ADMIN' => ['Administrador de empresa', 'Administra usuarios y datos de su propia empresa; no modifica seguridad global.'],
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
            ['empresa.editar', 'Editar empresa', 'EMPRESA', 'Actualizar datos y logo de la empresa asignada.'],
            ['usuarios.ver', 'Ver usuarios', 'USUARIOS', 'Listar usuarios de la propia empresa.'],
            ['usuarios.crear', 'Crear usuarios', 'USUARIOS', 'Crear cuentas dentro de la propia empresa.'],
            ['usuarios.editar', 'Editar usuarios', 'USUARIOS', 'Editar usuarios de la propia empresa.'],
            ['usuarios.estado', 'Cambiar estado de usuarios', 'USUARIOS', 'Activar o desactivar usuarios de la propia empresa.'],

            // Seguridad y empresas globales: reservadas para SUPERADMIN.
            ['empresas.ver', 'Ver empresas', 'SISTEMA', 'Consultar empresas registradas en AgroCloud.'],
            ['empresas.crear', 'Crear empresas', 'SISTEMA', 'Registrar nuevas empresas en AgroCloud.'],
            ['empresas.editar', 'Editar empresas', 'SISTEMA', 'Editar cualquier empresa desde administración global.'],
            ['empresas.estado', 'Cambiar estado de empresas', 'SISTEMA', 'Activar o desactivar empresas desde administración global.'],
            ['roles.ver', 'Ver roles', 'SEGURIDAD', 'Consultar roles globales del sistema.'],
            ['roles.editar', 'Asignar permisos a roles', 'SEGURIDAD', 'Configurar la matriz global de permisos.'],
            ['permisos.ver', 'Ver permisos', 'SEGURIDAD', 'Consultar el catálogo global de permisos.'],
            ['permisos.crear', 'Crear permisos', 'SEGURIDAD', 'Agregar permisos globales al catálogo.'],
            ['permisos.editar', 'Editar permisos', 'SEGURIDAD', 'Modificar permisos globales existentes.'],
            ['permisos.estado', 'Cambiar estado de permisos', 'SEGURIDAD', 'Activar o desactivar permisos globales.'],
        ];

        $permissionIds = [];
        foreach ($permissions as [$codigo, $nombre, $modulo, $descripcion]) {
            $permission = Permiso::query()->updateOrCreate(
                ['codigo' => $codigo],
                compact('nombre', 'modulo', 'descripcion') + ['estado' => true],
            );
            $permissionIds[$codigo] = $permission->id_permiso;
        }

        $superadmin = Rol::query()->where('codigo', 'SUPERADMIN')->firstOrFail();
        $superadmin->permisos()->sync(array_values($permissionIds));

        $admin = Rol::query()->where('codigo', 'ADMIN')->firstOrFail();
        $adminCodes = [
            'dashboard.ver',
            'empresa.ver',
            'empresa.editar',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.estado',
        ];
        $admin->permisos()->sync(array_values(array_intersect_key($permissionIds, array_flip($adminCodes))));

        $basicCodes = ['dashboard.ver', 'empresa.ver'];
        $basic = array_values(array_intersect_key($permissionIds, array_flip($basicCodes)));
        Rol::query()
            ->whereIn('codigo', ['RECEPCION', 'INSPECTOR', 'SUPERVISOR', 'PRODUCCION'])
            ->get()
            ->each(fn (Rol $rol) => $rol->permisos()->sync($basic));

        // Compatibilidad inicial: usuarios antiguos sin empresa se asocian una sola vez a
        // la primera empresa del caso actual. Los nuevos usuarios SIEMPRE se crean con empresa.
        User::query()
            ->whereNull('id_empresa')
            ->where(function ($query) {
                $query->whereDoesntHave('rol')
                    ->orWhereHas('rol', fn ($roleQuery) => $roleQuery->where('codigo', '!=', 'SUPERADMIN'));
            })
            ->update(['id_empresa' => $empresa->id_empresa]);

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
                    'cargo' => 'Administrador de empresa',
                    'password_hash' => $adminPassword,
                    'estado' => true,
                ],
            );
        }

        // No se crea un SUPERADMIN automáticamente. Queda reservado para una fase futura.
    }
}
