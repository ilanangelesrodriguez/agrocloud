<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user()?->loadMissing('rol');

        abort_unless($user && $user->hasAnyPermission($permissions), 403, 'No tienes permiso para realizar esta acción.');

        return $next($request);
    }
}
