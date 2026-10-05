<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanySecurityUpdated implements ShouldBroadcastNow, ShouldRescue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $empresaId,
        public string $resource,
        public string $action,
        public int|string|null $resourceId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('empresa.'.$this->empresaId.'.security'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'security.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'resource' => $this->resource,
            'action' => $this->action,
            'resource_id' => $this->resourceId,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
