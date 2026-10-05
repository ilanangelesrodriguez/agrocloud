<?php

use App\Constants\RouteNames;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->name(RouteNames::DASHBOARD);

    /*
    |--------------------------------------------------------------------------
    | Módulos futuros
    |--------------------------------------------------------------------------
    | Mantén cada módulo agrupado por prefix + name para que el sistema crezca
    | ordenadamente. Ejemplo:
    |
    | Route::prefix('lotes')->name('lotes.')->group(function () {
    |     Route::get('/', [LoteController::class, 'index'])->name('index');
    | });
    */
});
