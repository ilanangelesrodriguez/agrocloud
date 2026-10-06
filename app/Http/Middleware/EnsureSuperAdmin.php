<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            ($request->user()?->loadMissing('rol'))?->isSuperAdmin(),
            403,
            'Esta sección está reservada para la administración global de AgroCloud.'
        );

        return $next($request);
    }
}
