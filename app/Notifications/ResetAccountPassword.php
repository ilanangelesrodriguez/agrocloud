<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetAccountPassword extends ResetPassword
{
    public function toMail($notifiable)
    {
        return (new MailMessage)->subject('Restablece tu contraseña de AgroCloud AI')
            ->greeting('Hola, '.$notifiable->nombre)
            ->line('Recibimos una solicitud para restablecer tu contraseña.')
            ->action('Restablecer contraseña', route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line('El enlace vence en '.config('auth.passwords.users.expire', 60).' minutos y se utiliza una sola vez.')
            ->line('Si no solicitaste el cambio, puedes ignorar este mensaje.');
    }
}
