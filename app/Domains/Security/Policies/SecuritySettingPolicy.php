<?php

namespace App\Domains\Security\Policies;

use App\Models\User;
use App\Domains\Security\Models\SecuritySetting;

class SecuritySettingPolicy
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
     * Ver configuraciones de seguridad
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-settings.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver una configuración específica
     */
    public function view(User $user, SecuritySetting $setting): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-settings.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver configuraciones de login (públicas para mostrar en UI)
     */
    public function viewLoginSettings(User $user): bool
    {
        // Cualquier usuario autenticado puede ver configuraciones de login
        // para que la UI sepa qué opciones mostrar
        return true;
    }

    /**
     * Actualizar una configuración
     */
    public function update(User $user, ?SecuritySetting $setting = null): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-settings.update')) {
            return true;
        }

        return false;
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMany(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-settings.update')) {
            return true;
        }

        return false;
    }

    /**
     * Limpiar cache de configuraciones
     */
    public function clearCache(User $user): bool
    {
        if ($user->hasRole(['security', 'admin'])) {
            return true;
        }

        if ($user->hasPermissionTo('security-settings.update')) {
            return true;
        }

        return false;
    }
}
