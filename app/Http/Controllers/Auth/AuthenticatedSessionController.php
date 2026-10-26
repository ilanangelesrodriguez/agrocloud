<?php

namespace App\Http\Controllers\Auth;

use App\Constants\RouteNames;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $request->session()->put('account_auth_version', (int) $request->user()->auth_version);

        $request->user()?->forceFill([
            'ultimo_acceso' => now(),
        ])->saveQuietly();

        $context = hash_hmac('sha256', (string) $request->ip().'|'.substr((string) $request->userAgent(), 0, 500), (string) config('app.key'));
        $previous = \Illuminate\Support\Facades\DB::table('account_security_events')->where('id_usuario', $request->user()->id_usuario)->where('event', 'LOGIN_CONTEXT');
        $seen = (clone $previous)->where('changes->context_hash', $context)->exists();
        if (!$seen) {
            if ((clone $previous)->exists()) \App\Services\AccountSecurity::notice($request->user(), 'NEW_LOGIN_CONTEXT', 'Se inició sesión en AgroCloud desde una combinación de red y navegador que no habíamos registrado. Puede deberse a un cambio de conexión. Si no fuiste tú, cambia tu contraseña y contacta al administrador.');
            \App\Services\AccountSecurity::record($request->user(), 'LOGIN_CONTEXT', ['context_hash' => $context]);
        }

        $redirect = redirect()->intended(route(RouteNames::DASHBOARD));
        return $request->expectsJson() ? response()->json(['redirect' => $redirect->getTargetUrl()]) : $redirect;
    }

    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->expectsJson() ? response()->json(['redirect' => route(RouteNames::LOGIN)]) : redirect()->route(RouteNames::LOGIN);
    }
}
