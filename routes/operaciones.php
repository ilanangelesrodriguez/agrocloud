<?php

use App\Http\Controllers\OperacionController;
use App\Http\Controllers\ReporteOperativoController;
use App\Http\Middleware\EnsureCompanyOperator;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', EnsureCompanyOperator::class])->group(function () {
    Route::get('/incidencias', [\App\Http\Controllers\IncidenciaController::class, 'index'])->middleware('permission:lotes.ver')->name('incidencias.index');
    Route::get('/incidencias/lotes/{lote}/crear', [\App\Http\Controllers\IncidenciaController::class, 'crear'])->middleware('permission:lotes.ver')->name('incidencias.create');
    Route::post('/incidencias/lotes/{lote}', [\App\Http\Controllers\IncidenciaController::class, 'store'])->middleware('permission:lotes.ver')->name('incidencias.store');
    Route::post('/incidencias/{incidencia}/seguimiento', [\App\Http\Controllers\IncidenciaController::class, 'update'])->middleware('permission:lotes.ver')->name('incidencias.update');
    Route::get('/recepciones', \App\Livewire\RecepcionesIndex::class)->middleware('permission:recepciones.ver')->name('recepciones.index');
    Route::get('/recepciones/{lote}/revisar', [OperacionController::class, 'revisar'])->middleware('permission:recepciones.ver')->name('recepciones.revisar');
    Route::post('/recepciones/{lote}', [OperacionController::class, 'recibir'])->middleware('permission:recepciones.crear')->name('recepciones.store');
    Route::get('/trazabilidad/{lote}', [OperacionController::class, 'traza'])->middleware('permission:trazabilidad.ver')->name('trazabilidad.show');
    Route::get('/reportes/operativos', [ReporteOperativoController::class, 'index'])->middleware('permission:reportes.operativos')->name('reportes.operativos');
});
