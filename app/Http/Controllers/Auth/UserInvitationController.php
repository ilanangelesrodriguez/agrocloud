<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserInvitation;
use App\Support\PasswordPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserInvitationController extends Controller
{
    private function available(User $user, string $token): bool
    {
        return $user->estado && $user->rol?->estado && $user->empresa?->estado
            && $user->invitation_hash && $user->invitation_expires_at?->isFuture()
            && hash_equals($user->invitation_hash, hash('sha256', $token));
    }

    public function show(User $usuario, string $token)
    {
        abort_unless($this->available($usuario, $token), 410, 'La invitación expiró, ya se utilizó o la cuenta no está disponible. Solicita un reenvío al administrador.');
        return response()->view('auth.user-invitation', compact('usuario', 'token'))
            ->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function activate(Request $request, User $usuario, string $token)
    {
        $data = $request->validate(['password' => ['required', 'confirmed', ...PasswordPolicy::rules()]]);
        DB::transaction(function () use ($usuario, $token, $data) {
            $user = User::whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->available($user, $token), 410, 'Invitación no disponible. Solicita un nuevo enlace al administrador.');
            $user->forceFill(['password_hash' => $data['password'], 'invitation_hash' => null,
                'invitation_expires_at' => null, 'email_verified_at' => now(), 'remember_token' => Str::random(60)])->save();
        });
        return redirect()->route('login')->with('status', 'Cuenta activada. Ingresa con tu correo y la contraseña que acabas de crear.');
    }

    public function resend(Request $request, User $usuario, UserInvitation $invitations)
    {
        $actor = $request->user();
        abort_unless($actor->hasPermission('usuarios.editar'), 403);
        abort_unless($actor->isSuperAdmin() || ($actor->id_empresa && $actor->id_empresa === $usuario->id_empresa), 404);
        abort_if($usuario->isSuperAdmin(), 404);
        abort_unless($usuario->invitation_hash && $usuario->estado && $usuario->rol?->estado && $usuario->empresa?->estado, 422, 'La cuenta no tiene una invitación pendiente o está deshabilitada.');
        $message = $invitations->send($usuario);
        if ($request->header('X-User-Module') === 'true') {
            return response()->json(['saved' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }
}
