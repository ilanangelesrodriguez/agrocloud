<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()?->loadMissing('rol');

        return view('dashboard.index', compact('user'));
    }
}
