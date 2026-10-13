<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('empresa.{empresaId}.security', function (User $user, int $empresaId) {
    return $user->estado
        && (int) $user->id_empresa === $empresaId
        && (! $user->rol || $user->rol->estado);
});

// AGROCLOUD_NOTIFICATIONS_INSTALLED: canal personal y segregado por empresa.
Broadcast::channel('empresa.{empresaId}.usuario.{usuarioId}.notifications', function (User $user, int $empresaId, int $usuarioId) {
    return $user->estado
        && (int) $user->id_empresa === $empresaId
        && (int) $user->id_usuario === $usuarioId
        && $user->rol !== null
        && $user->rol->estado;
});
Broadcast::channel('plataforma.usuario.{usuarioId}.notifications', function (User $user, int $usuarioId) {
    return $user->estado
        && $user->id_empresa === null
        && (int) $user->id_usuario === $usuarioId
        && $user->isSuperAdmin();
});
