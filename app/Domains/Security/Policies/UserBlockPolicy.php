<?php

namespace App\Domains\Security\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\UserBlock;

class UserBlockPolicy
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
     * Ver lista de usuarios bloqueados
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver historial de bloqueos
     */
    public function viewHistory(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver estadísticas de bloqueos
     */
    public function viewStatistics(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver un bloqueo específico
     */
    public function view(User $user, UserBlock $block): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.view')) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si un usuario está bloqueado
     */
    public function checkBlock(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.view')) {
            return true;
        }

        return false;
    }

    /**
     * Bloquear un usuario
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.create')) {
            return true;
        }

        return false;
    }

    /**
     * Desbloquear un usuario
     */
    public function delete(User $user, UserBlock $block): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.delete')) {
            return true;
        }

        return false;
    }

    /**
     * Desbloquear un usuario (sin instancia de block)
     */
    public function unblock(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('user-blocks.delete')) {
            return true;
        }

        return false;
    }
}
