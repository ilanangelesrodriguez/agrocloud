<?php

use App\Constants\RouteNames;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route(RouteNames::DASHBOARD)
        : view('welcome');
});

require __DIR__.'/auth.php';
require __DIR__.'/app.php';
require __DIR__.'/inspector_workflow.php';
require __DIR__.'/maestros.php';
require __DIR__.'/operaciones.php';
require __DIR__.'/notificaciones.php'; // AGROCLOUD_NOTIFICATIONS_INSTALLED

require __DIR__.'/acceso.php';
