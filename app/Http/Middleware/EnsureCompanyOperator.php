<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** El flujo operativo siempre requiere una empresa; SUPERADMIN no puede cruzar tenants. */
class EnsureCompanyOperator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && ! $user->isSuperAdmin() && $user->id_empresa, 403,
            'Selecciona una cuenta empresarial para utilizar el módulo operativo.');

        return $next($request);
    }
}
