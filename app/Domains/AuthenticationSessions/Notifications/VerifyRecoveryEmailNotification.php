<?php

namespace App\Domains\AuthenticationSessions\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyRecoveryEmailNotification extends Notification
{
    use Queueable;

    public $code;

    /**
     * Create a new notification instance.
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verificación de Email de Recuperación - TechProc')
            ->greeting('¡Hola!')
            ->line('Has agregado este email como tu email de recuperación de contraseña.')
            ->line('Tu código de verificación es:')
            ->line('')
            ->line('**' . $this->code . '**')
            ->line('')
            ->line('Este código expirará en 15 minutos.')
            ->line('Si no solicitaste esto, puedes ignorar este correo.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
