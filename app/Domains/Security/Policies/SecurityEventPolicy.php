<?php

namespace App\Domains\Security\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\SecurityEvent;

class SecurityEventPolicy
{
    /**
     * Permite que admin haga todo
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * Ver lista de eventos propios
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ver TODOS los eventos de seguridad (de todos los usuarios)
     */
    public function viewAll(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-events.view-any')) {
            return true;
        }

        return false;
    }

    /**
     * Ver un evento específico
     */
    public function view(User $user, SecurityEvent $event): bool
    {
        // Puede ver si es su propio evento
        if ($event->user_id === $user->id) {
            return true;
        }

        // O si tiene rol de seguridad
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-events.view-any')) {
            return true;
        }

        return false;
    }

    /**
     * Ver estadísticas de eventos
     */
    public function viewStatistics(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-events.view-any')) {
            return true;
        }

        return false;
    }

    /**
     * Ver eventos críticos
     */
    public function viewCritical(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-events.view-any')) {
            return true;
        }

        return false;
    }
}
