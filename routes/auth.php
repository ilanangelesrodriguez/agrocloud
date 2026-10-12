<?php

use App\Constants\RouteNames;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ThemePreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name(RouteNames::LOGIN);

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name(RouteNames::LOGIN_STORE);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name(RouteNames::LOGOUT);

Route::put('/perfil/tema', [ThemePreferenceController::class, 'update'])->middleware(['auth', 'active'])->name('perfil.tema.update');
