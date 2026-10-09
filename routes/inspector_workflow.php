<?php

use App\Http\Controllers\CalidadController;
use App\Http\Controllers\EvidenciaController;
use App\Http\Controllers\InspeccionController;
use App\Http\Controllers\MuestreoController;
use App\Http\Controllers\ValidacionInspeccionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', \App\Http\Middleware\EnsureCompanyOperator::class])->group(function () {
    Route::prefix('muestreos')->name('muestreos.')->group(function () {
        Route::get('/', [MuestreoController::class, 'index'])->middleware('permission:muestreos.ver')->name('index');
        Route::get('/crear', [MuestreoController::class, 'create'])->middleware('permission:muestreos.crear')->name('create');
        Route::post('/', [MuestreoController::class, 'store'])->middleware('permission:muestreos.crear')->name('store');
        Route::get('/{muestreo}', [MuestreoController::class, 'show'])->middleware('permission:muestreos.ver')->name('show');
        Route::post('/{muestreo}/inspecciones', [InspeccionController::class, 'store'])->middleware('permission:inspecciones.crear')->name('inspecciones.store');
    });

    Route::prefix('inspecciones')->name('inspecciones.')->group(function () {
        Route::get('/', [InspeccionController::class, 'index'])->middleware('permission:inspecciones.ver')->name('index');
        Route::get('/{inspeccion}', [InspeccionController::class, 'show'])->middleware('permission:inspecciones.ver')->name('show');
        Route::get('/{inspeccion}/evidencias', [EvidenciaController::class, 'index'])->middleware('permission:inspecciones.ver')->name('evidencias');
        Route::post('/{inspeccion}/evidencias', [EvidenciaController::class, 'store'])->middleware('permission:inspecciones.capturar')->name('evidencias.store');
        Route::post('/{inspeccion}/evidencias/{evidencia}/analizar', [EvidenciaController::class, 'analizar'])->middleware('permission:inspecciones.analizar')->name('evidencias.analizar');
        Route::get('/{inspeccion}/validacion', [ValidacionInspeccionController::class, 'index'])->middleware('permission:inspecciones.ver')->name('validacion');
        Route::post('/{inspeccion}/validacion/{evidencia}', [ValidacionInspeccionController::class, 'validarDeteccion'])->middleware('permission:inspecciones.validar')->name('validacion.detectar');
        Route::post('/{inspeccion}/hallazgos', [ValidacionInspeccionController::class, 'hallazgo'])->middleware('permission:inspecciones.validar')->name('hallazgos.store');
        Route::post('/{inspeccion}/finalizar', [ValidacionInspeccionController::class, 'finalizar'])->middleware('permission:inspecciones.validar')->name('finalizar');
    });

    Route::prefix('calidad')->name('calidad.')->group(function () {
        Route::get('/pendientes', [CalidadController::class, 'index'])->middleware('permission:calidad.ver')->name('index');
        Route::get('/inspecciones/{inspeccion}', [CalidadController::class, 'show'])->middleware('permission:calidad.ver')->name('show');
        Route::post('/inspecciones/{inspeccion}/dictamen', [CalidadController::class, 'store'])->middleware('permission:calidad.clasificar')->name('dictamen.store');
    });
});
