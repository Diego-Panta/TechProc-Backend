<?php

namespace App\Domains\Users\Policies;

use App\Domains\AuthenticationSessions\Models\User;

class UserPolicy
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
     * Determina si el usuario puede ver la lista de usuarios
     */
    public function viewAny(User $user): bool
    {
        //return true;
        return $user->hasPermissionTo('users.view');
    }

    /**
     * Determina si el usuario puede ver un usuario específico
     */
    public function view(User $user, User $model): bool
    {
        // Permitir si tiene el permiso O si es su propio perfil
        return $user->hasPermissionTo('users.view-any') || $user->id === $model->id;
    }

    /**
     * Determina si el usuario puede crear usuarios
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    /**
     * Determina si el usuario puede actualizar un usuario
     */
    public function update(User $user, User $model): bool
    {
        // Puede actualizar si:
        // 1. Tiene el permiso users.update, O
        // 2. Es su propio perfil

        if ($user->hasPermissionTo('users.update')) {
            return true;
        }

        // Permitir editar su propio perfil
        return $user->id === $model->id;
    }

    /**
     * Determina si el usuario puede eliminar un usuario
     */
    public function delete(User $user, User $model): bool
    {
        // No puede eliminarse a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        // No puede eliminar a super_admin (a menos que sea super_admin)
        if ($model->hasRole('super_admin') && !$user->hasRole('super_admin')) {
            return false;
        }

        // Debe tener el permiso
        return $user->hasPermissionTo('users.delete');
    }

    /**
     * Determina si el usuario puede asignar roles
     */
    public function assignRoles(User $user, User $model): bool
    {
        // No puede asignar roles a sí mismo
        if ($user->id === $model->id && !$user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermissionTo('users.assign-roles');
    }

    /**
     * Determina si el usuario puede asignar permisos
     */
    public function assignPermissions(User $user, User $model): bool
    {
        // No puede asignar permisos a sí mismo
        if ($user->id === $model->id && !$user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasPermissionTo('users.assign-permissions');
    }
}
