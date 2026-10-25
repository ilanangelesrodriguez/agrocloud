<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChanged extends Notification
{
    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Tu contraseña de AgroCloud AI cambió')
            ->greeting('Hola, '.$notifiable->nombre)
            ->line('Se actualizó la contraseña de tu cuenta. Tus sesiones anteriores se cerrarán.')
            ->line('Si no realizaste este cambio, recupera tu contraseña y contacta a la administración.')
            ->action('Recuperar acceso', route('password.request'));
    }
}
