<?php

use App\Constants\RouteNames;
use App\Http\Controllers\Admin\EmpresaAdminController;
use App\Http\Controllers\Admin\EmpresaUsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.ver')
        ->name(RouteNames::DASHBOARD);

    Route::get('/perfil', [PerfilController::class, 'edit'])->name(RouteNames::PROFILE);
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password.update');

    // Contexto de empresa del usuario autenticado. No existe selector de empresa para usuarios normales.
    Route::get('/empresa', [EmpresaController::class, 'edit'])
        ->middleware('permission:empresa.ver')
        ->name(RouteNames::EMPRESA);
    Route::put('/empresa', [EmpresaController::class, 'update'])
        ->middleware('permission:empresa.editar')
        ->name('empresa.update');

    // Gestión de usuarios de la propia empresa.
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])
            ->middleware('permission:usuarios.ver')
            ->name('index');
        Route::get('/crear', [UsuarioController::class, 'create'])
            ->middleware('permission:usuarios.crear')
            ->name('create');
        Route::post('/', [UsuarioController::class, 'store'])
            ->middleware('permission:usuarios.crear')
            ->name('store');
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

    Route::middleware('superadmin')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Administración global multiempresa
        |--------------------------------------------------------------------------
        | SUPERADMIN no pertenece a una empresa. Selecciona explícitamente el tenant
        | que administra mediante la URL /admin/empresas/{empresa}.
        */
        Route::prefix('admin/empresas')->name('admin.empresas.')->group(function () {
            Route::get('/', [EmpresaAdminController::class, 'index'])->name('index');
            Route::get('/crear', [EmpresaAdminController::class, 'create'])->name('create');
            Route::post('/', [EmpresaAdminController::class, 'store'])->name('store');

            // Exportables globales. Se declaran antes de /{empresa} para evitar colisiones.
            Route::get('/exportar/excel', [EmpresaAdminController::class, 'exportExcel'])->name('export.excel');
            Route::get('/exportar/csv', [EmpresaAdminController::class, 'exportCsv'])->name('export.csv');
            Route::get('/exportar/reporte', [EmpresaAdminController::class, 'report'])->name('export.report');

            Route::get('/{empresa}', [EmpresaAdminController::class, 'show'])->name('show');
            Route::get('/{empresa}/editar', [EmpresaAdminController::class, 'edit'])->name('edit');
            Route::put('/{empresa}', [EmpresaAdminController::class, 'update'])->name('update');
            Route::patch('/{empresa}/estado', [EmpresaAdminController::class, 'toggleStatus'])->name('toggle-status');

            Route::prefix('{empresa}/usuarios')->name('usuarios.')->group(function () {
                Route::get('/', [EmpresaUsuarioController::class, 'index'])->name('index');
                Route::get('/crear', [EmpresaUsuarioController::class, 'create'])->name('create');
                Route::post('/', [EmpresaUsuarioController::class, 'store'])->name('store');

                Route::get('/exportar/excel', [EmpresaUsuarioController::class, 'exportExcel'])->name('export.excel');
                Route::get('/exportar/csv', [EmpresaUsuarioController::class, 'exportCsv'])->name('export.csv');
                Route::get('/exportar/reporte', [EmpresaUsuarioController::class, 'report'])->name('export.report');

                Route::get('/{usuario}/editar', [EmpresaUsuarioController::class, 'edit'])->name('edit');
                Route::put('/{usuario}', [EmpresaUsuarioController::class, 'update'])->name('update');
                Route::patch('/{usuario}/estado', [EmpresaUsuarioController::class, 'toggleStatus'])->name('toggle-status');
            });
        });

        // Catálogo global de seguridad.
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolController::class, 'index'])->name('index');
            Route::get('/{rol}/editar', [RolController::class, 'edit'])->name('edit');
            Route::put('/{rol}', [RolController::class, 'update'])->name('update');
        });

        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/', [PermisoController::class, 'index'])->name('index');
            Route::get('/crear', [PermisoController::class, 'create'])->name('create');
            Route::post('/', [PermisoController::class, 'store'])->name('store');
            Route::get('/{permiso}/editar', [PermisoController::class, 'edit'])->name('edit');
            Route::put('/{permiso}', [PermisoController::class, 'update'])->name('update');
            Route::patch('/{permiso}/estado', [PermisoController::class, 'toggleStatus'])->name('toggle-status');
        });
    });
});
