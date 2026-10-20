<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolicitudAccesoController;
use App\Http\Controllers\Admin\SolicitudAccesoController as AdminSolicitudes;

Route::middleware('guest')->group(function () {
    Route::get('/solicitar-acceso', [SolicitudAccesoController::class, 'crear'])->name('acceso.create');
    Route::post('/solicitar-acceso', [SolicitudAccesoController::class, 'guardar'])->middleware('throttle:3,60')->name('acceso.store');
    Route::view('/solicitud-recibida', 'acceso.recibida')->name('acceso.recibida');
    Route::get('/activar-acceso/{solicitud}/{token}', [SolicitudAccesoController::class, 'invitacion'])->whereNumber('solicitud')->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:20,1')->name('acceso.invitacion');
    Route::post('/activar-acceso/{solicitud}/{token}', [SolicitudAccesoController::class, 'activar'])->whereNumber('solicitud')->where('token', '[A-Za-z0-9]{64}')->middleware('throttle:5,1')->name('acceso.activar');
});
Route::middleware(['auth', 'active', 'superadmin'])->prefix('admin/solicitudes-acceso')->name('admin.solicitudes.')->group(function () {
    Route::get('/', [AdminSolicitudes::class, 'index'])->name('index');
    Route::get('/{solicitud}', [AdminSolicitudes::class, 'show'])->whereNumber('solicitud')->name('show');
    Route::post('/{solicitud}/decidir', [AdminSolicitudes::class, 'decidir'])->whereNumber('solicitud')->name('decidir');
    Route::post('/{solicitud}/correo', [AdminSolicitudes::class, 'reenviar'])->whereNumber('solicitud')->middleware('throttle:3,10')->name('reenviar');
});
