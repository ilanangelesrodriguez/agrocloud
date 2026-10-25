<?php

use App\Http\Controllers\SamplingProcedureController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->prefix('procedimientos-muestreo')->name('sampling.procedures.')->group(function () {
    Route::get('/', [SamplingProcedureController::class, 'index'])->name('index');
    Route::post('/', [SamplingProcedureController::class, 'store'])->name('store');
    Route::patch('/{procedure}', [SamplingProcedureController::class, 'toggle'])->whereNumber('procedure')->name('toggle');
});
