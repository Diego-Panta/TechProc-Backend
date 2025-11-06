<?php

namespace App\Domains\Users\Policies;

use App\Domains\AuthenticationSessions\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
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
     * Ver todos los roles
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('roles.view');
    }

    /**
     * Ver un rol específico
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('roles.view-any');
    }

    /**
     * Crear roles
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('roles.create');
    }

    /**
     * Actualizar roles
     */
    public function update(User $user, Role $role): bool
    {
        // No puede editar el rol super_admin a menos que sea super_admin
        if ($role->name === 'super_admin' && !$user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermissionTo('roles.update');
    }

    /**
     * Eliminar roles
     */
    public function delete(User $user, Role $role): bool
    {
        // No puede eliminar el rol super_admin
        if ($role->name === 'super_admin') {
            return false;
        }

        // No puede eliminar roles protegidos
        $protectedRoles = ['super_admin', 'admin'];
        if (in_array($role->name, $protectedRoles) && !$user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermissionTo('roles.delete');
    }

    /**
     * Asignar permisos a roles
     */
    public function assignPermissions(User $user, Role $role): bool
    {
        // No puede modificar permisos del super_admin a menos que sea super_admin
        if ($role->name === 'super_admin' && !$user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermissionTo('roles.assign-permissions');
    }
}
