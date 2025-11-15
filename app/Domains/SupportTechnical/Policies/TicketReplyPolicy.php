<?php

namespace App\Domains\SupportTechnical\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\TicketReply;

class TicketReplyPolicy
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
     * Determina si el usuario puede ver respuestas
     */
    public function viewAny(User $user): bool
    {
        // Si puede ver el ticket, puede ver las respuestas
        return true;
    }

    /**
     * Determina si el usuario puede ver una respuesta específica
     */
    public function view(User $user, TicketReply $reply): bool
    {
        // Puede ver si puede ver el ticket asociado
        $ticket = $reply->ticket;
        
        if (!$ticket) {
            return false;
        }

        // Es el propietario del ticket
        if ($ticket->user_id === $user->id) {
            return true;
        }

        // Tiene permiso para ver todos los tickets
        if ($user->hasPermissionTo('ticket-replies.view-any')) {
            return true;
        }

        // Es soporte o admin
        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear respuestas
     */
    public function create(User $user): bool
    {
        // Si tiene acceso al ticket, puede responder
        // La validación específica se hace en el controller
        return true;
    }

    /**
     * Determina si el usuario puede actualizar una respuesta
     */
    public function update(User $user, TicketReply $reply): bool
    {
        // Soporte con permiso puede actualizar cualquier respuesta
        if ($user->hasPermissionTo('ticket-replies.update') && $user->hasRole(['support', 'admin'])) {
            return true;
        }

        // Solo el autor puede actualizar su respuesta
        // La validación de 24 horas se hace en el Service para dar mensaje personalizado
        if ($reply->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar una respuesta
     */
    public function delete(User $user, TicketReply $reply): bool
    {
        // La primera respuesta no se puede eliminar (se valida en el service)
        
        // El autor puede eliminar su propia respuesta
        if ($reply->user_id === $user->id) {
            return true;
        }

        // Soporte con permiso puede eliminar
        if ($user->hasPermissionTo('ticket-replies.delete')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }
}
