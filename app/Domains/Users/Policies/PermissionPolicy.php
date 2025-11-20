<?php

namespace App\Domains\Users\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
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
     * Ver todos los permisos
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('permissions.view');
    }

    /**
     * Ver un permiso específico
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permissions.view-any');
    }

    /**
     * Crear permisos
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('permissions.create');
    }

    /**
     * Actualizar permisos
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permissions.update');
    }

    /**
     * Eliminar permisos
     */
    public function delete(User $user, Permission $permission): bool
    {
        // Prevenir eliminar permisos críticos del sistema
        $criticalPermissions = [
            'users.view',
            'users.view-any',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-roles',
            'users.assign-permissions',
            'roles.view',
            'roles.view-any',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign-permissions',
            'permissions.view',
            'permissions.view-any',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
        ];

        if (in_array($permission->name, $criticalPermissions)) {
            return false;
        }

        return $user->hasPermissionTo('permissions.delete');
    }
}
