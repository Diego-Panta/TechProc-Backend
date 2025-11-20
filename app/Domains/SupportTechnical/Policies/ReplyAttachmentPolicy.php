<?php

namespace App\Domains\SupportTechnical\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\ReplyAttachment;

class ReplyAttachmentPolicy
{
    /**
     * Permite que super_admin haga todo
     * Este método se ejecuta ANTES que todos los demás
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null; // Continuar con los métodos normales
    }

    /**
     * Determina si el usuario puede ver adjuntos
     */
    public function viewAny(User $user): bool
    {
        // Si puede ver las respuestas, puede ver los adjuntos
        return true;
    }

    /**
     * Determina si el usuario puede ver un adjunto específico
     */
    public function view(User $user, ReplyAttachment $attachment): bool
    {
        // Puede ver si tiene acceso al ticket
        $reply = $attachment->ticketReply;
        
        if (!$reply || !$reply->ticket) {
            return false;
        }

        $ticket = $reply->ticket;

        // Es el propietario del ticket
        if ($ticket->user_id === $user->id) {
            return true;
        }

        // Tiene permiso para ver todos los adjuntos
        if ($user->hasPermissionTo('reply-attachments.view-any')) {
            return true;
        }

        // Es soporte o admin
        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede descargar un adjunto
     */
    public function download(User $user, ReplyAttachment $attachment): bool
    {
        // Mismo permiso que view
        return $this->view($user, $attachment);
    }

    /**
     * Determina si el usuario puede crear adjuntos
     */
    public function create(User $user): bool
    {
        // Si puede crear respuestas, puede crear adjuntos
        // La validación específica se hace en el controller
        return true;
    }

    /**
     * Determina si el usuario puede eliminar un adjunto
     */
    public function delete(User $user, ReplyAttachment $attachment): bool
    {
        $reply = $attachment->ticketReply;
        
        if (!$reply) {
            return false;
        }

        // El autor de la respuesta puede eliminar sus adjuntos
        if ($reply->user_id === $user->id) {
            return true;
        }

        // Soporte con permiso puede eliminar
        if ($user->hasPermissionTo('reply-attachments.delete')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }
}
