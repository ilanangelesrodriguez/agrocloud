<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendEmailChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailChange
{
    // Prepare the pending address inside the same transaction as the profile edit.
    public function prepare(User $user, array &$data): void
    {
        if (!isset($data['correo']) || $data['correo'] === $user->correo) return;
        $token = Str::random(64);
        $user->forceFill(['pending_email' => $data['correo'], 'email_change_hash' => hash('sha256', $token), 'email_change_expires_at' => now()->addHours(24)]);
        $data['correo'] = $user->correo;
        DB::afterCommit(function () use ($user, $token) {
            try {
                $id = DB::table('security_mail_attempts')->insertGetId(['id_usuario' => $user->id_usuario, 'kind' => 'EMAIL_VERIFICATION', 'status' => 'queued', 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
                SendEmailChange::dispatch($user->id_usuario, $token, $id);
            } catch (\Throwable $error) {
                if (isset($id)) DB::table('security_mail_attempts')->where('id', $id)->update(['status' => 'failed', 'updated_at' => now()]);
                report($error);
            }
        });
    }
}
