<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class SecurityAndCompanySeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $roles = [
            'SUPERADMIN' => [
                'Superadministrador AgroCloud',
                'Rol reservado para administración global de la plataforma y futuras empresas.',
            ],

            'ADMIN' => [
                'Administrador de empresa',
                'Administra usuarios y datos de su propia empresa; no modifica seguridad global.',
            ],

            'RECEPCION' => [
                'Responsable de recepción',
                'Gestiona la recepción de lotes.',
            ],

            'INSPECTOR' => [
                'Inspector de control de calidad',
                'Realiza inspecciones, evidencias y validaciones.',
            ],

            'SUPERVISOR' => [
                'Supervisor de Pre-Empaque',
                'Supervisa el proceso de Pre-Empaque.',
            ],

            'PRODUCCION' => [
                'Responsable de producción',
                'Consulta y gestiona información de producción.',
            ],
        ];

        foreach ($roles as $codigo => [$nombre, $descripcion]) {
            Rol::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'estado' => true,
                ],
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PERMISOS
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Dashboard
            [
                'dashboard.ver',
                'Ver dashboard',
                'DASHBOARD',
                'Acceder al dashboard principal.',
            ],

            // Empresa
            [
                'empresa.ver',
                'Ver empresa',
                'EMPRESA',
                'Consultar la empresa asignada.',
            ],

            [
                'empresa.editar',
                'Editar empresa',
                'EMPRESA',
                'Actualizar datos y logo de la empresa asignada.',
            ],

            // Usuarios
            [
                'usuarios.ver',
                'Ver usuarios',
                'USUARIOS',
                'Listar usuarios de la propia empresa.',
            ],

            [
                'usuarios.crear',
                'Crear usuarios',
                'USUARIOS',
                'Crear cuentas dentro de la propia empresa.',
            ],

            [
                'usuarios.editar',
                'Editar usuarios',
                'USUARIOS',
                'Editar usuarios de la propia empresa.',
            ],

            [
                'usuarios.estado',
                'Cambiar estado de usuarios',
                'USUARIOS',
                'Activar o desactivar usuarios de la propia empresa.',
            ],

            // Productores
            [
                'productores.ver',
                'Ver productores',
                'PRODUCTORES',
                'Permite consultar el listado y detalle de productores.',
            ],

            [
                'productores.crear',
                'Crear productores',
                'PRODUCTORES',
                'Permite registrar nuevos productores.',
            ],

            [
                'productores.editar',
                'Editar productores',
                'PRODUCTORES',
                'Permite editar la información de los productores.',
            ],

            [
                'productores.eliminar',
                'Eliminar productores',
                'PRODUCTORES',
                'Permite eliminar productores que no tengan lotes asociados.',
            ],

            [
                'productores.lotes.ver',
                'Ver lotes del productor',
                'PRODUCTORES',
                'Permite consultar los lotes asociados a un productor.',
            ],
            // Lotes
            [
                'lotes.ver',
                'Ver lotes',
                'LOTES',
                'Permite consultar el listado y detalle de lotes.',
            ],

            [
                'lotes.crear',
                'Crear lotes',
                'LOTES',
                'Permite registrar nuevos lotes.',
            ],

            [
                'lotes.editar',
                'Editar lotes',
                'LOTES',
                'Permite editar la información de los lotes.',
            ],

            [
                'lotes.eliminar',
                'Eliminar lotes',
                'LOTES',
                'Permite eliminar lotes que no tengan muestreos ni dictámenes de calidad asociados.',
            ],


            // Seguridad y empresas globales:
            // reservadas para SUPERADMIN.
            [
                'empresas.ver',
                'Ver empresas',
                'SISTEMA',
                'Consultar empresas registradas en AgroCloud.',
            ],

            [
                'empresas.crear',
                'Crear empresas',
                'SISTEMA',
                'Registrar nuevas empresas en AgroCloud.',
            ],

            [
                'empresas.editar',
                'Editar empresas',
                'SISTEMA',
                'Editar cualquier empresa desde administración global.',
            ],

            [
                'empresas.estado',
                'Cambiar estado de empresas',
                'SISTEMA',
                'Activar o desactivar empresas desde administración global.',
            ],

            [
                'roles.ver',
                'Ver roles',
                'SEGURIDAD',
                'Consultar roles globales del sistema.',
            ],

            [
                'roles.editar',
                'Asignar permisos a roles',
                'SEGURIDAD',
                'Configurar la matriz global de permisos.',
            ],

            [
                'permisos.ver',
                'Ver permisos',
                'SEGURIDAD',
                'Consultar el catálogo global de permisos.',
            ],

            [
                'permisos.crear',
                'Crear permisos',
                'SEGURIDAD',
                'Agregar permisos globales al catálogo.',
            ],

            [
                'permisos.editar',
                'Editar permisos',
                'SEGURIDAD',
                'Modificar permisos globales existentes.',
            ],

            [
                'permisos.estado',
                'Cambiar estado de permisos',
                'SEGURIDAD',
                'Activar o desactivar permisos globales.',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | CREAR / ACTUALIZAR PERMISOS
        |--------------------------------------------------------------------------
        */

        $permissionIds = [];

        foreach ($permissions as [$codigo, $nombre, $modulo, $descripcion]) {
            $permission = Permiso::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'modulo' => $modulo,
                    'descripcion' => $descripcion,
                    'estado' => true,
                ],
            );

            $permissionIds[$codigo] = $permission->id_permiso;
        }

        /*
        |--------------------------------------------------------------------------
        | SUPERADMIN
        |--------------------------------------------------------------------------
        */

        $superadmin = Rol::query()
            ->where('codigo', 'SUPERADMIN')
            ->firstOrFail();

        $superadmin->permisos()->sync(
            array_values($permissionIds)
        );

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = Rol::query()
            ->where('codigo', 'ADMIN')
            ->firstOrFail();

        $adminCodes = [
            'dashboard.ver',
            'empresa.ver',
            'empresa.editar',

            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.estado',

            // Productores
            'productores.ver',
            'productores.crear',
            'productores.editar',
            'productores.eliminar',
            'productores.lotes.ver',



           // Lotes
           'lotes.ver',
           'lotes.crear',
           'lotes.editar',
           'lotes.eliminar',
        ];



        $admin->permisos()->sync(
            array_values(
                array_intersect_key(
                    $permissionIds,
                    array_flip($adminCodes)
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | RECEPCION / INSPECTOR / SUPERVISOR / PRODUCCION
        |--------------------------------------------------------------------------
        |
        | Por ahora mantienen solamente los permisos básicos.
        | Los permisos específicos de Productores se asignarán
        | cuando terminemos de definir la matriz de permisos.
        |
        */

        $basicCodes = [
            'dashboard.ver',
            'empresa.ver',
        ];

        $recepcionCodes = [
            'dashboard.ver',
            'empresa.ver',
            'productores.ver',
            'productores.crear',
            'productores.editar',
            'productores.lotes.ver',


            // Lotes
            'lotes.ver',
            'lotes.crear',
            'lotes.editar',
        ];

        $supervisorCodes = [
            'dashboard.ver',
            'empresa.ver',
            'productores.ver',
            'productores.lotes.ver',
        ];

        $produccionCodes = [
            'dashboard.ver',
            'empresa.ver',
            'productores.ver',
            'productores.lotes.ver',
        ];

        $basic = array_values(
            array_intersect_key(
                $permissionIds,
                array_flip($basicCodes)
            )
        );

       /*
       |--------------------------------------------------------------------------
       | RECEPCION
       |--------------------------------------------------------------------------
       */

       $recepcion = Rol::query()
           ->where('codigo', 'RECEPCION')
           ->firstOrFail();

       $recepcion->permisos()->sync(
           array_values(
               array_intersect_key(
                   $permissionIds,
                   array_flip($recepcionCodes)
               )
           )
       );

       /*
       |--------------------------------------------------------------------------
       | SUPERVISOR
       |--------------------------------------------------------------------------
       */

       $supervisor = Rol::query()
           ->where('codigo', 'SUPERVISOR')
           ->firstOrFail();

       $supervisor->permisos()->sync(
           array_values(
               array_intersect_key(
                   $permissionIds,
                   array_flip($supervisorCodes)
               )
           )
       );

       /*
       |--------------------------------------------------------------------------
       | PRODUCCION
       |--------------------------------------------------------------------------
       */

       $produccion = Rol::query()
           ->where('codigo', 'PRODUCCION')
           ->firstOrFail();

       $produccion->permisos()->sync(
           array_values(
               array_intersect_key(
                   $permissionIds,
                   array_flip($produccionCodes)
               )
           )
       );

       /*
       |--------------------------------------------------------------------------
       | INSPECTOR
       |--------------------------------------------------------------------------
       |
       | El inspector conserva por ahora solamente sus permisos básicos.
       |
       */

       $inspector = Rol::query()
           ->where('codigo', 'INSPECTOR')
           ->firstOrFail();

       $inspector->permisos()->sync($basic);

    }
}
