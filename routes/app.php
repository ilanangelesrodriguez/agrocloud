<?php

use App\Constants\RouteNames;
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

    // Cuenta personal: no permite cambiar empresa, rol, correo ni estado.
    Route::get('/perfil', [PerfilController::class, 'edit'])->name(RouteNames::PROFILE);
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.password.update');

    // Empresa actual. Los usuarios normales nunca seleccionan otra empresa.
    Route::get('/empresa', [EmpresaController::class, 'edit'])
        ->middleware('permission:empresa.ver')
        ->name(RouteNames::EMPRESA);
    Route::put('/empresa', [EmpresaController::class, 'update'])
        ->middleware('permission:empresa.editar')
        ->name('empresa.update');

    // Gestión de usuarios siempre limitada a la empresa autenticada.
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

    // Catálogo global de seguridad: reservado para el SUPERADMIN futuro.
    Route::middleware('superadmin')->group(function () {
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
