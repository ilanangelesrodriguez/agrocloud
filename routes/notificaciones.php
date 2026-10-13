<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->prefix('notificaciones')->name('notificaciones.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/feed', [NotificationController::class, 'feed'])->name('feed');
    Route::post('/leer-todas', [NotificationController::class, 'leerTodas'])->name('leer-todas');
    Route::get('/{notification}/abrir', [NotificationController::class, 'abrir'])->name('abrir');
    Route::post('/{notification}/leer', [NotificationController::class, 'leer'])->name('leer');
});
