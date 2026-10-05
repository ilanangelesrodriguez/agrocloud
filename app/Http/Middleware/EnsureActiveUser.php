<?php

namespace App\Http\Middleware;

use App\Constants\RouteNames;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()?->loadMissing(['rol', 'empresa']);

        $inactiveUser = ! $user || ! $user->estado;
        $inactiveRole = $user?->rol && ! $user->rol->estado;
        $inactiveCompany = $user?->empresa && ! $user->empresa->estado;

        if ($inactiveUser || $inactiveRole || $inactiveCompany) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route(RouteNames::LOGIN)
                ->withErrors(['correo' => 'Tu acceso está inactivo. Comunícate con el administrador.']);
        }

        return $next($request);
    }
}
