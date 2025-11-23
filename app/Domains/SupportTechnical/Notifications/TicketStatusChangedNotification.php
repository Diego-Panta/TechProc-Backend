<?php

namespace App\Domains\SupportTechnical\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use IncadevUns\CoreDomain\Models\Ticket;

class TicketStatusChangedNotification extends Notification
{
    use Queueable;

    protected Ticket $ticket;
    protected string $oldStatus;
    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $oldStatus, string $newStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusLabels = [
            'open' => 'Abierto',
            'pending' => 'Pendiente',
            'closed' => 'Cerrado'
        ];

        $oldStatusLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newStatusLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        $url = env('FRONTEND_URL', 'http://localhost:4321') . '/tecnologico/tickets/' . $this->ticket->id;

        $message = (new MailMessage)
            ->subject('Estado de Ticket Actualizado - TechProc')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('El estado de tu ticket ha sido actualizado.')
            ->line('**Ticket:** ' . $this->ticket->title)
            ->line('**Estado anterior:** ' . $oldStatusLabel)
            ->line('**Estado nuevo:** ' . $newStatusLabel);

        // Mensaje adicional según el nuevo estado
        if ($this->newStatus === 'closed') {
            $message->line('Tu ticket ha sido cerrado. Si necesitas más ayuda, puedes reabrir el ticket o crear uno nuevo.');
        } elseif ($this->newStatus === 'pending') {
            $message->line('Tu ticket está siendo revisado por nuestro equipo de soporte.');
        } elseif ($this->newStatus === 'open') {
            $message->line('Tu ticket ha sido reabierto y será atendido próximamente.');
        }

        $message->action('Ver Ticket', $url)
                ->salutation('Saludos, ' . config('app.name'));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
