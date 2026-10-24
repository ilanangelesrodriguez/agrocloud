<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolicitudAccesoController;
use App\Http\Controllers\Admin\SolicitudAccesoController as AdminSolicitudes;

Route::middleware('guest')->group(function () {
    Route::get('/solicitar-acceso', [SolicitudAccesoController::class, 'crear'])->name('acceso.create');
    Route::post('/solicitar-acceso/consulta-ruc', \App\Http\Controllers\ConsultaRucController::class)
        ->middleware(['throttle:5,1', 'throttle:ruc-public'])->name('acceso.consulta-ruc');
    // Límite antiabuso por IP. Se aumenta de 3 a 10/h para redes compartidas;
    // los duplicados se controlan por RUC y correo en el servidor.
    Route::post('/solicitar-acceso', [SolicitudAccesoController::class, 'guardar'])->middleware('throttle:10,60')->name('acceso.store');
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
