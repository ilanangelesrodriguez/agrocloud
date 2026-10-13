<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('empresa.{empresaId}.security', function (User $user, int $empresaId) {
    return $user->estado
        && (int) $user->id_empresa === $empresaId
        && (! $user->rol || $user->rol->estado);
});
