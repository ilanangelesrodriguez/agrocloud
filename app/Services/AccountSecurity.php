<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendSecurityNotice;
use Illuminate\Support\Facades\DB;

class AccountSecurity
{
    public static function record(User $user, string $event, array $changes = []): void
    {
        DB::table('account_security_events')->insert(['id_empresa' => $user->id_empresa, 'id_usuario' => $user->id_usuario,
            'actor_id' => auth()->id(), 'event' => $event, 'changes' => json_encode($changes, JSON_UNESCAPED_UNICODE), 'created_at' => now()]);
    }

    public static function notice(User $user, string $kind, string $message, ?string $address = null): void
    {
        DB::afterCommit(function () use ($user, $kind, $message, $address) {
            try {
                $id = DB::table('security_mail_attempts')->insertGetId(['id_usuario' => $user->id_usuario, 'kind' => $kind,
                    'status' => 'queued', 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
                SendSecurityNotice::dispatch($id, $address ?? $user->correo, 'Seguridad de tu cuenta AgroCloud', $message);
            } catch (\Throwable $error) {
                if (isset($id)) DB::table('security_mail_attempts')->where('id', $id)->update(['status' => 'failed', 'updated_at' => now()]);
                report($error);
            }
        });
    }
}
