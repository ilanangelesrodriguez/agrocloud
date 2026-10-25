<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AccountSecurity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountObserver
{
    public function updating(User $user): void
    {
        if ($user->isDirty(['password_hash', 'id_rol', 'estado', 'correo'])) {
            $user->auth_version = (int) $user->getRawOriginal('auth_version') + 1;
            $user->remember_token = Str::random(60);
        }
    }

    public function updated(User $user): void
    {
        $changes = [];
        foreach (['id_rol', 'estado', 'correo', 'pending_email'] as $field) {
            if ($user->wasChanged($field)) $changes[$field] = ['antes' => $user->getRawOriginal($field), 'despues' => $user->getAttribute($field)];
        }
        if ($user->wasChanged('estado')) $changes['motivo'] = request()->input('motivo_seguridad') ?: 'Cambio de estado mediante proceso interno.';
        if ($user->wasChanged('password_hash')) $changes['password_changed'] = true;
        if (!$changes) return;
        AccountSecurity::record($user, 'ACCOUNT_UPDATED', $changes);
        if ($user->wasChanged('auth_version') && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id_usuario)->delete();
        }
        if ($user->wasChanged(['id_rol', 'estado'])) {
            AccountSecurity::notice($user, 'ACCESS_CHANGED', 'Se modificó el rol o el estado de tu cuenta. Estado: '.($user->estado ? 'habilitada' : 'deshabilitada').'. Si no esperabas este cambio, contacta al administrador.');
        }
    }
}
