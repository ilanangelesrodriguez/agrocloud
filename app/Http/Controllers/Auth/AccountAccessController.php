<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UniqueUserEmail;
use App\Services\{AccountSecurity, EmailChange};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Password, Validator};

class AccountAccessController extends Controller
{
    public function recovery(Request $request, User $usuario)
    {
        $actor = $request->user();
        abort_unless($actor->hasPermission('usuarios.editar'), 403);
        abort_unless($actor->isSuperAdmin() || ($actor->id_empresa && $actor->id_empresa === $usuario->id_empresa), 404);
        abort_if($usuario->isSuperAdmin() && !$usuario->is($actor), 404);
        abort_unless($usuario->estado && !$usuario->invitation_hash && $usuario->rol?->estado && ($usuario->isSuperAdmin() || $usuario->empresa?->estado), 422, 'La cuenta debe estar activada y habilitada. Para cuentas pendientes utiliza Reenviar invitación.');
        try {
            $mailer = config('mail.default');
            if (in_array(config("mail.mailers.$mailer.transport", $mailer), ['log', 'array', 'failover'], true)) throw new \RuntimeException('Transporte sin envío real.');
            $status = Password::sendResetLink(['correo' => $usuario->correo, 'estado' => true]);
            AccountSecurity::record($usuario, 'ADMIN_RECOVERY_REQUESTED');
            $message = $status === Password::RESET_LINK_SENT ? 'El proveedor aceptó el enlace de recuperación. El usuario elegirá su contraseña.' : 'No se envió otro enlace. Espera antes de reintentar o revisa la cuenta.';
        } catch (\Throwable $error) { report($error); $message = 'No se pudo enviar el enlace. Revisa la configuración del correo y vuelve a intentarlo.'; }
        if ($request->header('X-User-Module') === 'true') return response()->json(['saved' => true, 'message' => $message]);
        return back()->with('success', $message);
    }

    public function requestEmail(Request $request, EmailChange $change)
    {
        $request->merge(['correo' => mb_strtolower(trim((string) $request->input('correo')))]);
        $data = $request->validate(['correo' => ['required', 'email', 'max:255', new UniqueUserEmail($request->user()->id_usuario)], 'current_password' => ['required', 'current_password:web']]);
        unset($data['current_password']);
        DB::transaction(function () use ($request, $change, &$data) {
            $user = User::whereKey($request->user()->getKey())->lockForUpdate()->firstOrFail();
            $change->prepare($user, $data);
            $user->save();
        });
        return back()->with('success', 'Si cambiaste la dirección, revisa el nuevo correo y confirma el enlace de 24 horas. El correo actual sigue siendo válido hasta confirmar. Si no llega, puedes repetir la solicitud.');
    }

    private function available(User $user, string $token): bool
    {
        return $user->estado && !$user->invitation_hash && $user->rol?->estado && ($user->isSuperAdmin() || $user->empresa?->estado)
            && $user->pending_email && $user->email_change_expires_at?->isFuture()
            && hash_equals((string) $user->email_change_hash, hash('sha256', $token));
    }

    public function showEmail(User $usuario, string $token)
    {
        abort_unless($this->available($usuario, $token), 410, 'El enlace expiró, fue reemplazado o la cuenta no está disponible.');
        return response()->view('auth.confirm-email', compact('usuario', 'token'))->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function cancelEmail(Request $request, User $usuario)
    {
        $actor = $request->user();
        if (!$usuario->is($actor)) {
            abort_unless($actor->hasPermission('usuarios.editar'), 403);
            abort_unless($actor->isSuperAdmin() || ($actor->id_empresa && $actor->id_empresa === $usuario->id_empresa && !$usuario->isSuperAdmin()), 404);
        }
        $usuario->forceFill(['pending_email' => null, 'email_change_hash' => null, 'email_change_expires_at' => null])->save();
        $message = 'Solicitud de cambio de correo cancelada. Los enlaces anteriores ya no pueden utilizarse.';
        if ($request->header('X-User-Module') === 'true') return response()->json(['saved' => true, 'message' => $message]);
        return back()->with('success', $message);
    }

    public function confirmEmail(User $usuario, string $token)
    {
        DB::transaction(function () use ($usuario, $token) {
            $user = User::whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->available($user, $token), 410, 'El enlace ya no está disponible.');
            Validator::make(['correo' => $user->pending_email], ['correo' => [new UniqueUserEmail($user->id_usuario)]])->validate();
            $old = $user->correo;
            $user->forceFill(['correo' => $user->pending_email, 'pending_email' => null, 'email_change_hash' => null,
                'email_change_expires_at' => null, 'email_verified_at' => now()])->save();
            AccountSecurity::record($user, 'EMAIL_CHANGE_CONFIRMED');
            AccountSecurity::notice($user, 'EMAIL_CHANGED', 'Se confirmó un cambio de correo para tu cuenta de AgroCloud. Si no lo autorizaste, contacta inmediatamente al administrador.', $old);
        });
        return redirect()->route('login')->with('status', 'Correo confirmado. Inicia sesión con tu nueva dirección.');
    }

    public function audit(Request $request, User $usuario)
    {
        $actor = $request->user();
        abort_unless($actor->hasPermission('usuarios.editar'), 403);
        abort_unless($actor->isSuperAdmin() || ($actor->id_empresa && $actor->id_empresa === $usuario->id_empresa && !$usuario->isSuperAdmin()), 404);
        $events = DB::table('account_security_events')->where('id_usuario', $usuario->id_usuario)->latest('id')->paginate(20);
        $mail = DB::table('security_mail_attempts')->where('id_usuario', $usuario->id_usuario)->latest('id')->limit(20)->get();
        return view('usuarios.security', compact('usuario', 'events', 'mail'));
    }
}
