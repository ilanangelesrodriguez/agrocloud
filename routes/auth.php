<?php

use App\Constants\RouteNames;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ThemePreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/recuperar-contrasena', [\App\Http\Controllers\Auth\PasswordRecoveryController::class, 'create'])->name('password.request');
    Route::post('/recuperar-contrasena', [\App\Http\Controllers\Auth\PasswordRecoveryController::class, 'store'])->middleware('throttle:50,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [\App\Http\Controllers\Auth\PasswordRecoveryController::class, 'edit'])->name('password.reset');
    Route::post('/restablecer-contrasena', [\App\Http\Controllers\Auth\PasswordRecoveryController::class, 'update'])->middleware('throttle:50,1')->name('password.update');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name(RouteNames::LOGIN);

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name(RouteNames::LOGIN_STORE);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name(RouteNames::LOGOUT);

Route::put('/perfil/tema', [ThemePreferenceController::class, 'update'])->middleware(['auth', 'active'])->name('perfil.tema.update');

Route::get('/invitacion-usuario/{usuario}/{token}', [\App\Http\Controllers\Auth\UserInvitationController::class, 'show'])
    ->whereNumber('usuario')->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:20,1')->name('users.invitation');
Route::post('/invitacion-usuario/{usuario}/{token}', [\App\Http\Controllers\Auth\UserInvitationController::class, 'activate'])
    ->whereNumber('usuario')->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:20,1')->name('users.invitation.activate');
Route::post('/usuarios/{usuario}/reenviar-invitacion', [\App\Http\Controllers\Auth\UserInvitationController::class, 'resend'])
    ->whereNumber('usuario')->middleware(['auth', 'active', 'throttle:5,1'])->name('users.invitation.resend');
