<?php

use App\Constants\RouteNames;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->name(RouteNames::DASHBOARD);

    Route::get('/empresa', [EmpresaController::class, 'edit'])
        ->middleware('permission:empresa.ver')
        ->name(RouteNames::EMPRESA);
    Route::put('/empresa', [EmpresaController::class, 'update'])
        ->middleware('permission:empresa.editar')
        ->name('empresa.update');

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

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolController::class, 'index'])
            ->middleware('permission:roles.ver')
            ->name('index');
        Route::get('/{rol}/editar', [RolController::class, 'edit'])
            ->middleware('permission:roles.editar')
            ->name('edit');
        Route::put('/{rol}', [RolController::class, 'update'])
            ->middleware('permission:roles.editar')
            ->name('update');
    });

    Route::prefix('permisos')->name('permisos.')->group(function () {
        Route::get('/', [PermisoController::class, 'index'])
            ->middleware('permission:permisos.ver')
            ->name('index');
        Route::get('/crear', [PermisoController::class, 'create'])
            ->middleware('permission:permisos.crear')
            ->name('create');
        Route::post('/', [PermisoController::class, 'store'])
            ->middleware('permission:permisos.crear')
            ->name('store');
        Route::get('/{permiso}/editar', [PermisoController::class, 'edit'])
            ->middleware('permission:permisos.editar')
            ->name('edit');
        Route::put('/{permiso}', [PermisoController::class, 'update'])
            ->middleware('permission:permisos.editar')
            ->name('update');
        Route::patch('/{permiso}/estado', [PermisoController::class, 'toggleStatus'])
            ->middleware('permission:permisos.estado')
            ->name('toggle-status');
    });
});
