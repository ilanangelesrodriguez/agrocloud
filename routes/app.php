<?php

use App\Constants\RouteNames;
use App\Http\Controllers\Admin\EmpresaAdminController;
use App\Http\Controllers\Admin\EmpresaUsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\ProductorController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.ver')
        ->name(RouteNames::DASHBOARD);

    /*
    |--------------------------------------------------------------------------
    | PERFIL
    |--------------------------------------------------------------------------
    */

    Route::get('/perfil', [PerfilController::class, 'edit'])
        ->name(RouteNames::PROFILE);

    Route::put('/perfil', [PerfilController::class, 'update'])
        ->name('perfil.update');

    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])
        ->name('perfil.password.update');

    /*
    |--------------------------------------------------------------------------
    | EMPRESA
    |--------------------------------------------------------------------------
    |
    | Contexto de empresa del usuario autenticado.
    | No existe selector de empresa para usuarios normales.
    |
    */

    Route::get('/empresa', [EmpresaController::class, 'edit'])
        ->middleware('permission:empresa.ver')
        ->name(RouteNames::EMPRESA);

    Route::put('/empresa', [EmpresaController::class, 'update'])
        ->middleware('permission:empresa.editar')
        ->name('empresa.update');

    /*
    |--------------------------------------------------------------------------
    | USUARIOS
    |--------------------------------------------------------------------------
    |
    | Gestión de usuarios de la propia empresa.
    |
    */

    Route::prefix('usuarios')
        ->name('usuarios.')
        ->group(function () {

            Route::get('/', \App\Livewire\UsuariosIndex::class)
                ->middleware('permission:usuarios.ver')
                ->name('index');

            Route::get('/crear', [UsuarioController::class, 'create'])
                ->middleware('permission:usuarios.crear')
                ->name('create');

            Route::post('/', [UsuarioController::class, 'store'])
                ->middleware('permission:usuarios.crear')
                ->name('store');

            Route::get('/{usuario}/detalle', [UsuarioController::class, 'show'])
                ->middleware('permission:usuarios.ver')
                ->name('show');

            Route::get('/{usuario}/editar', [UsuarioController::class, 'edit'])
                ->middleware('permission:usuarios.editar')
                ->name('edit');

            Route::put('/{usuario}', [UsuarioController::class, 'update'])
                ->middleware('permission:usuarios.editar')
                ->name('update');

            Route::patch('/{usuario}/estado', [UsuarioController::class, 'toggleStatus'])
                ->middleware('permission:usuarios.estado')
                ->name('toggle-status');
        });

    /*
    |--------------------------------------------------------------------------
    | PRODUCTORES
    |--------------------------------------------------------------------------
    |
    | Listado, búsqueda, creación, detalle, lotes y edición.
    |
    */

    Route::prefix('productores')
        ->name('productores.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | LISTADO
            |--------------------------------------------------------------------------
            */

            Route::get('/', \App\Livewire\ProductoresIndex::class)
                ->middleware('permission:productores.ver')
                ->name('index');

            /*
            |--------------------------------------------------------------------------
            | CREAR
            |--------------------------------------------------------------------------
            |
            | Estas rutas van antes de /{productor} para evitar
            | conflictos con el parámetro dinámico.
            |
            */

            Route::get('/crear/nuevo', [ProductorController::class, 'create'])
                ->middleware('permission:productores.crear')
                ->name('create');

            Route::post('/', [ProductorController::class, 'store'])
                ->middleware('permission:productores.crear')
                ->name('store');

            /*
            |--------------------------------------------------------------------------
            | EDITAR
            |--------------------------------------------------------------------------
            */

            Route::get('/{productor}/editar', [ProductorController::class, 'edit'])
                ->middleware('permission:productores.editar')
                ->name('edit');

            Route::put('/{productor}', [ProductorController::class, 'update'])
                ->middleware('permission:productores.editar')
                ->name('update');

            /*
            |--------------------------------------------------------------------------
            | LOTES DEL PRODUCTOR
            |--------------------------------------------------------------------------
            */

            Route::get('/{productor}/lotes', [ProductorController::class, 'lotes'])
                ->middleware('permission:productores.lotes.ver')
                ->name('lotes');

            /*
            |--------------------------------------------------------------------------
            | DETALLE
            |--------------------------------------------------------------------------
            */

            Route::get('/{productor}', [ProductorController::class, 'show'])
                ->middleware('permission:productores.ver')
                ->name('show');
                /*
                |--------------------------------------------------------------------------
                | ELIMINAR
                |--------------------------------------------------------------------------
                */

                Route::delete('/{productor}', [ProductorController::class, 'destroy'])
                    ->middleware('permission:productores.eliminar')
                    ->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | LOTES
        |--------------------------------------------------------------------------
        |
        | Gestión de lotes asociados a productores.
        |
        */

        Route::prefix('lotes')
            ->name('lotes.')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | LISTADO
                |--------------------------------------------------------------------------
                */

                Route::get('/', \App\Livewire\LotesIndex::class)
                    ->middleware('permission:lotes.ver')
                    ->name('index');

                /*
                |--------------------------------------------------------------------------
                | CREAR
                |--------------------------------------------------------------------------
                */

                Route::get('/crear', [LoteController::class, 'create'])
                    ->middleware('permission:lotes.crear')
                    ->name('create');

                Route::post('/', [LoteController::class, 'store'])
                    ->middleware('permission:lotes.crear')
                    ->name('store');

                /*
                |--------------------------------------------------------------------------
                | EDITAR
                |--------------------------------------------------------------------------
                */

                Route::get('/{lote}/editar', [LoteController::class, 'edit'])
                    ->middleware('permission:lotes.editar')
                    ->name('edit');

                Route::put('/{lote}', [LoteController::class, 'update'])
                    ->middleware('permission:lotes.editar')
                    ->name('update');

                /*
                |--------------------------------------------------------------------------
                | DETALLE
                |--------------------------------------------------------------------------
                */

                Route::get('/{lote}', [LoteController::class, 'show'])
                    ->middleware('permission:lotes.ver')
                    ->name('show');

                /*
                |--------------------------------------------------------------------------
                | ELIMINAR
                |--------------------------------------------------------------------------
                */

                Route::delete('/{lote}', [LoteController::class, 'destroy'])
                    ->middleware('permission:lotes.eliminar')
                    ->name('destroy');
            });

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACIÓN GLOBAL - SUPERADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('superadmin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Administración global multiempresa
        |--------------------------------------------------------------------------
        |
        | SUPERADMIN no pertenece a una empresa.
        | Selecciona explícitamente el tenant que administra mediante:
        |
        | /admin/empresas/{empresa}
        |
        */

        Route::prefix('admin/empresas')
            ->name('admin.empresas.')
            ->group(function () {

                Route::get('/', [EmpresaAdminController::class, 'index'])
                    ->name('index');

                Route::get('/crear', [EmpresaAdminController::class, 'create'])
                    ->name('create');

                Route::post('/', [EmpresaAdminController::class, 'store'])
                    ->name('store');

                /*
                |--------------------------------------------------------------------------
                | Exportables globales
                |--------------------------------------------------------------------------
                |
                | Se declaran antes de /{empresa} para evitar colisiones.
                |
                */

                Route::get('/exportar/excel', [EmpresaAdminController::class, 'exportExcel'])
                    ->name('export.excel');

                Route::get('/exportar/csv', [EmpresaAdminController::class, 'exportCsv'])
                    ->name('export.csv');

                Route::get('/exportar/reporte', [EmpresaAdminController::class, 'report'])
                    ->name('export.report');

                /*
                |--------------------------------------------------------------------------
                | Empresa específica
                |--------------------------------------------------------------------------
                */

                Route::get('/{empresa}', [EmpresaAdminController::class, 'show'])
                    ->name('show');

                Route::get('/{empresa}/editar', [EmpresaAdminController::class, 'edit'])
                    ->name('edit');

                Route::put('/{empresa}', [EmpresaAdminController::class, 'update'])
                    ->name('update');

                Route::patch('/{empresa}/estado', [EmpresaAdminController::class, 'toggleStatus'])
                    ->name('toggle-status');

                /*
                |--------------------------------------------------------------------------
                | Usuarios de empresa administrados por SUPERADMIN
                |--------------------------------------------------------------------------
                */

                Route::prefix('{empresa}/usuarios')
                    ->name('usuarios.')
                    ->group(function () {

                        Route::get('/', [EmpresaUsuarioController::class, 'index'])
                            ->name('index');

                        Route::get('/crear', [EmpresaUsuarioController::class, 'create'])
                            ->name('create');

                        Route::post('/', [EmpresaUsuarioController::class, 'store'])
                            ->name('store');

                        Route::get('/exportar/excel', [EmpresaUsuarioController::class, 'exportExcel'])
                            ->name('export.excel');

                        Route::get('/exportar/csv', [EmpresaUsuarioController::class, 'exportCsv'])
                            ->name('export.csv');

                        Route::get('/exportar/reporte', [EmpresaUsuarioController::class, 'report'])
                            ->name('export.report');

                        Route::get('/{usuario}/editar', [EmpresaUsuarioController::class, 'edit'])
                            ->name('edit');

                        Route::put('/{usuario}', [EmpresaUsuarioController::class, 'update'])
                            ->name('update');

                        Route::patch('/{usuario}/estado', [EmpresaUsuarioController::class, 'toggleStatus'])
                            ->name('toggle-status');
                    });
            });

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        Route::prefix('roles')
            ->name('roles.')
            ->group(function () {

                Route::get('/', [RolController::class, 'index'])
                    ->name('index');

                Route::get('/{rol}/editar', [RolController::class, 'edit'])
                    ->name('edit');

                Route::put('/{rol}', [RolController::class, 'update'])
                    ->name('update');
            });

        /*
        |--------------------------------------------------------------------------
        | PERMISOS
        |--------------------------------------------------------------------------
        */

        Route::prefix('permisos')
            ->name('permisos.')
            ->group(function () {

                Route::get('/', [PermisoController::class, 'index'])
                    ->name('index');

                Route::get('/crear', [PermisoController::class, 'create'])
                    ->name('create');

                Route::post('/', [PermisoController::class, 'store'])
                    ->name('store');

                Route::get('/{permiso}/editar', [PermisoController::class, 'edit'])
                    ->name('edit');

                Route::put('/{permiso}', [PermisoController::class, 'update'])
                    ->name('update');

                Route::patch('/{permiso}/estado', [PermisoController::class, 'toggleStatus'])
                    ->name('toggle-status');
            });
    });
});
