<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OperationalAlert extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $empresaId,
        public readonly string $kind,
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly string $priority = 'normal',
        public readonly ?int $loteId = null,
    ) {}

    // Persistencia independiente de la disponibilidad de Reverb.
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'tipo' => $this->kind,
            'titulo' => $this->title,
            'mensaje' => $this->message,
            'url' => $this->url,
            'prioridad' => $this->priority,
            'lote_id' => $this->loteId,
        ];
    }
}
