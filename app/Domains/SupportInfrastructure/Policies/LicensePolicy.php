<?php

namespace App\Domains\SupportInfrastructure\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\License;

class LicensePolicy
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
     * Ver lista de licencias
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('licenses.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver una licencia específica
     */
    public function view(User $user, License $license): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('licenses.view')) {
            return true;
        }

        return false;
    }

    /**
     * Crear licencias
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('licenses.create')) {
            return true;
        }

        return false;
    }

    /**
     * Actualizar licencias
     */
    public function update(User $user, License $license): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('licenses.update')) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar licencias
     */
    public function delete(User $user, License $license): bool
    {
        if ($user->hasRole('infrastructure')) {
            return true;
        }

        if ($user->hasPermissionTo('licenses.delete')) {
            return true;
        }

        return false;
    }
}
