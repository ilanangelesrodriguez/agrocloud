<?php

use App\Http\Controllers\MaestroController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->prefix('maestros')->name('maestros.')->group(function () {
    Route::get('/', [MaestroController::class, 'index'])->name('index');
    Route::get('/{tipo}/comprobar-nombre', [MaestroController::class, 'checkName'])->name('check-name');
    Route::get('/{tipo}/crear', [MaestroController::class, 'create'])->name('create');
    Route::get('/{tipo}/{item}/editar', [MaestroController::class, 'edit'])->whereNumber('item')->name('edit');
    Route::post('/{tipo}', [MaestroController::class, 'store'])->name('store');
    Route::post('/{tipo}/opciones', [MaestroController::class, 'storeOption'])->name('store-option');
    Route::put('/{tipo}/{item}', [MaestroController::class, 'update'])->whereNumber('item')->name('update');
    Route::patch('/{tipo}/{item}/estado', [MaestroController::class, 'toggle'])->whereNumber('item')->name('toggle');
    Route::get('/{tipo}', [MaestroController::class, 'show'])->name('show');
});
