<?php

namespace App\Services;

use App\Events\UserNotificationsChanged;
use App\Models\User;
use App\Notifications\OperationalAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Envío dirigido por empresa y rol, posterior al commit del proceso de negocio.
 * Una falla del WebSocket o de las notificaciones no revierte operaciones agrícolas.
 */
class OperationalNotifier
{
    public static function roles(
        int $empresaId,
        array $roles,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $url,
        string $prioridad = 'normal',
        ?int $loteId = null,
        ?int $excluirUsuarioId = null,
    ): void {
        self::schedule(function () use ($empresaId, $roles, $tipo, $titulo, $mensaje, $url, $prioridad, $loteId, $excluirUsuarioId) {
            $query = User::query()->where('id_empresa', $empresaId)->where('estado', true)
                ->whereHas('rol', fn ($q) => $q->where('estado', true)->whereIn('codigo', $roles));
            if ($excluirUsuarioId) {
                $query->where('id_usuario', '!=', $excluirUsuarioId);
            }

            $query->orderBy('id_usuario')->chunkById(100, function ($users) use ($empresaId, $tipo, $titulo, $mensaje, $url, $prioridad, $loteId) {
                self::deliver($users->all(), new OperationalAlert($empresaId, $tipo, $titulo, $mensaje, $url, $prioridad, $loteId));
            }, 'id_usuario');
        });
    }

    /** Avisa a otros administradores globales, sin mezclar empresas. */
    public static function platform(string $tipo, string $titulo, string $mensaje, string $url, ?int $excludeId = null): void
    {
        self::schedule(function () use ($tipo, $titulo, $mensaje, $url, $excludeId) {
            $query = User::query()->whereNull('id_empresa')->where('estado', true)
                ->whereHas('rol', fn ($q) => $q->where('estado', true)->where('codigo', 'SUPERADMIN'));
            if ($excludeId) $query->where('id_usuario', '!=', $excludeId);
            $query->orderBy('id_usuario')->chunkById(100, function ($users) use ($tipo, $titulo, $mensaje, $url) {
                self::deliver($users->all(), new OperationalAlert(0, $tipo, $titulo, $mensaje, $url));
            }, 'id_usuario');
        });
    }

    public static function user(
        int $empresaId,
        ?int $usuarioId,
        string $tipo,
        string $titulo,
        string $mensaje,
        string $url,
        string $prioridad = 'normal',
        ?int $loteId = null,
    ): void {
        if (!$usuarioId) {
            return;
        }
        self::schedule(function () use ($empresaId, $usuarioId, $tipo, $titulo, $mensaje, $url, $prioridad, $loteId) {
            $user = User::query()->where('id_empresa', $empresaId)->where('id_usuario', $usuarioId)
                ->where('estado', true)->whereHas('rol', fn ($q) => $q->where('estado', true))->first();
            if ($user) {
                self::deliver([$user], new OperationalAlert($empresaId, $tipo, $titulo, $mensaje, $url, $prioridad, $loteId));
            }
        });
    }

    private static function schedule(\Closure $callback): void
    {
        $safe = static function () use ($callback): void {
            try {
                $callback();
            } catch (Throwable $e) {
                report($e);
            }
        };
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($safe);
        } else {
            $safe();
        }
    }

    private static function deliver(array $users, OperationalAlert $alert): void
    {
        if (!$users) {
            return;
        }
        // notification database channel no depende del queue worker.
        Notification::sendNow($users, $alert);
        if (config('broadcasting.default') !== 'reverb') {
            return;
        }
        foreach ($users as $user) {
            try {
                event(new UserNotificationsChanged($user->id_empresa === null ? null : (int) $user->id_empresa, (int) $user->id_usuario));
            } catch (Throwable $e) {
                report($e);
            }
        }
    }
}
