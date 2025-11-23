<?php

namespace App\Domains\Security\Policies;

use App\Models\User;
use App\Domains\Security\Models\UserSession;
use Laravel\Sanctum\PersonalAccessToken;

class SessionPolicy
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
     * Ver lista de sesiones propias
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ver TODAS las sesiones (de todos los usuarios)
     */
    public function viewAll(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('sessions.view-any')) {
            return true;
        }

        return false;
    }

    /**
     * Ver una sesión específica
     */
    public function view(User $user, $session): bool
    {
        // Determinar el user_id según el tipo de sesión
        $sessionUserId = $session->user_id ?? $session->tokenable_id ?? null;

        // Puede ver si es su propia sesión
        if ($sessionUserId === $user->id) {
            return true;
        }

        // O si tiene rol de seguridad
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('sessions.view-any')) {
            return true;
        }

        return false;
    }

    /**
     * Ver sesiones sospechosas
     */
    public function viewSuspicious(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('sessions.view-any')) {
            return true;
        }

        return false;
    }

    /**
     * Terminar una sesión específica
     */
    public function terminate(User $user, $session): bool
    {
        $sessionUserId = $session->user_id ?? $session->tokenable_id ?? null;

        // Puede terminar su propia sesión
        if ($sessionUserId === $user->id) {
            return true;
        }

        // O si tiene permiso para gestionar sesiones
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('sessions.terminate')) {
            return true;
        }

        return false;
    }

    /**
     * Terminar todas las sesiones de un usuario
     */
    public function terminateAll(User $user, ?int $targetUserId = null): bool
    {
        // Puede terminar sus propias sesiones
        if ($targetUserId === null || $targetUserId === $user->id) {
            return true;
        }

        // Para terminar sesiones de otros usuarios necesita permisos
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('sessions.terminate')) {
            return true;
        }

        return false;
    }
}
