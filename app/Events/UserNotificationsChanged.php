<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationsChanged implements ShouldBroadcastNow, ShouldRescue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ?int $empresaId, public int $usuarioId) {}

    public function broadcastOn(): array
    {
        return $this->empresaId !== null
            ? [new PrivateChannel("empresa.{$this->empresaId}.usuario.{$this->usuarioId}.notifications")]
            : [new PrivateChannel("plataforma.usuario.{$this->usuarioId}.notifications")];
    }

    public function broadcastAs(): string
    {
        return 'notifications.changed';
    }

    // Nunca enviar contenido sensible por WebSocket; el cliente lee la bandeja con auth.
    public function broadcastWith(): array
    {
        return ['updated' => true];
    }
}
