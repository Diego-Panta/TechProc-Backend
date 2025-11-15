<?php

namespace App\Domains\SupportTechnical\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\Ticket;

class TicketPolicy
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
     * Determina si el usuario puede ver la lista de tickets
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver tickets
        // (la lista completa o solo los suyos se determina en el controller)
        return true;
    }

    /**
     * Determina si el usuario puede ver TODOS los tickets (no solo los propios)
     */
    public function viewAll(User $user): bool
    {
        // Solo pueden ver todos los tickets:
        // 1. Usuarios con permiso tickets.view-any, O
        // 2. Usuarios con rol support o admin
        
        if ($user->hasPermissionTo('tickets.view-any')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver un ticket específico
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Puede ver si:
        // 1. Es el propietario del ticket, O
        // 2. Tiene el permiso tickets.view-any (soporte), O
        // 3. Tiene rol de support o admin
        
        if ($ticket->user_id === $user->id) {
            return true;
        }

        if ($user->hasPermissionTo('tickets.view-any')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede crear tickets
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear tickets
        return true;
    }

    /**
     * Determina si el usuario puede actualizar un ticket
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Puede actualizar si:
        // 1. Es el propietario (solo título), O
        // 2. Tiene permiso tickets.update (puede actualizar todo)
        
        if ($ticket->user_id === $user->id) {
            return true; // Solo podrá actualizar el título (se valida en el service)
        }

        if ($user->hasPermissionTo('tickets.update')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede cerrar un ticket
     */
    public function close(User $user, Ticket $ticket): bool
    {
        // Puede cerrar si:
        // 1. Es el propietario del ticket, O
        // 2. Tiene permiso tickets.update, O
        // 3. Es soporte o admin
        
        if ($ticket->user_id === $user->id) {
            return true;
        }

        if ($user->hasPermissionTo('tickets.update')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede reabrir un ticket
     */
    public function reopen(User $user, Ticket $ticket): bool
    {
        // Puede reabrir si:
        // 1. Es el propietario del ticket, O
        // 2. Tiene permiso tickets.update, O
        // 3. Es soporte o admin
        
        if ($ticket->user_id === $user->id) {
            return true;
        }

        if ($user->hasPermissionTo('tickets.update')) {
            return true;
        }

        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede eliminar un ticket
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Solo soporte con permiso explícito puede eliminar tickets
        if ($user->hasPermissionTo('tickets.delete')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ver estadísticas
     */
    public function viewStatistics(User $user): bool
    {
        // Solo soporte y admin pueden ver estadísticas
        if ($user->hasRole(['support', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('tickets.view-any')) {
            return true;
        }

        return false;
    }
}
