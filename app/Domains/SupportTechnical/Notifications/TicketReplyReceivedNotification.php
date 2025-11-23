<?php

namespace App\Domains\SupportTechnical\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use IncadevUns\CoreDomain\Models\Ticket;
use IncadevUns\CoreDomain\Models\TicketReply;

class TicketReplyReceivedNotification extends Notification
{
    use Queueable;

    protected Ticket $ticket;
    protected TicketReply $reply;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketReply $reply)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
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
        $url = env('FRONTEND_URL', 'http://localhost:4321') . '/tecnologico/tickets/' . $this->ticket->id;

        // Obtener el nombre del autor de la respuesta
        $replyAuthor = $this->reply->user ? $this->reply->user->name : 'Equipo de Soporte';

        // Truncar el contenido de la respuesta para el preview
        $replyPreview = strlen($this->reply->content) > 150
            ? substr($this->reply->content, 0, 150) . '...'
            : $this->reply->content;

        return (new MailMessage)
            ->subject('Nueva Respuesta en tu Ticket - TechProc')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Has recibido una nueva respuesta en tu ticket.')
            ->line('**Ticket:** ' . $this->ticket->title)
            ->line('**Respuesta de:** ' . $replyAuthor)
            ->line('**Mensaje:**')
            ->line($replyPreview)
            ->action('Ver Respuesta Completa', $url)
            ->line('Puedes responder directamente desde el enlace anterior.')
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'reply_id' => $this->reply->id,
            'reply_author_id' => $this->reply->user_id,
            'reply_author_name' => $this->reply->user ? $this->reply->user->name : null,
        ];
    }
}
