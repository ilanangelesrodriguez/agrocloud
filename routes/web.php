<?php

use App\Constants\RouteNames;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route(RouteNames::DASHBOARD)
        : redirect()->route(RouteNames::LOGIN);
});

require __DIR__.'/auth.php';
require __DIR__.'/app.php';
require __DIR__.'/inspector_workflow.php';
