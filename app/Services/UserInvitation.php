<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\{DB, Mail};
use Illuminate\Support\Str;

class UserInvitation
{
    public function send(User $user): string
    {
        $user->forceFill(['invitation_attempted_at' => now()])->save();
        try {
            $mailer = config('mail.default');
            if (in_array(config("mail.mailers.$mailer.transport", $mailer), ['log', 'array', 'failover'], true)) {
                throw new \RuntimeException('El transporte no permite confirmar un envío de invitación.');
            }
            DB::transaction(function () use ($user) {
                $user = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();
                abort_unless($user->invitation_hash && $user->estado && $user->rol?->estado && $user->empresa?->estado, 422);
                $token = Str::random(64);
                $url = route('users.invitation', ['usuario' => $user, 'token' => $token]);
                $user->forceFill(['invitation_hash' => hash('sha256', $token), 'invitation_expires_at' => now()->addHours(72)])->save();
                Mail::send('emails.user-invitation', compact('user', 'url'), fn ($mail) => $mail->to($user->correo)->subject('Activa tu cuenta de AgroCloud'));
                $user->forceFill(['invitation_sent_at' => now(), 'invitation_delivery_status' => 'provider_accepted'])->save();
                AccountSecurity::record($user, 'INVITATION_PROVIDER_ACCEPTED');
            });
            return 'Invitación enviada (aceptada por el proveedor; entrega no confirmada). El enlace vence en 72 horas; revisa también el correo no deseado.';
        } catch (\Throwable $error) {
            report($error);
            $user->refresh()->forceFill(['invitation_delivery_status' => 'failed'])->save();
            AccountSecurity::record($user, 'INVITATION_FAILED');
            return 'La cuenta sigue pendiente de activación. No se pudo enviar la invitación; revisa el correo configurado y reintenta desde Editar usuario. Si existía un enlace anterior, se conserva.';
        }
    }
}
