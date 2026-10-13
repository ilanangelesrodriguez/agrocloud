<?php

use App\Http\Controllers\OperacionController;
use App\Http\Controllers\ReporteOperativoController;
use App\Http\Middleware\EnsureCompanyOperator;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', EnsureCompanyOperator::class])->group(function () {
    Route::get('/recepciones', [OperacionController::class, 'recepciones'])->middleware('permission:recepciones.ver')->name('recepciones.index');
    Route::post('/recepciones/{lote}', [OperacionController::class, 'recibir'])->middleware('permission:recepciones.crear')->name('recepciones.store');
    Route::get('/trazabilidad/{lote}', [OperacionController::class, 'traza'])->middleware('permission:trazabilidad.ver')->name('trazabilidad.show');
    Route::get('/reportes/operativos', [ReporteOperativoController::class, 'index'])->middleware('permission:reportes.operativos')->name('reportes.operativos');
});
