<?php

namespace App\Domains\SupportInfrastructure\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\Software;

class SoftwarePolicy
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
     * Ver lista de software
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('software.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver un software específico
     */
    public function view(User $user, Software $software): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('software.view')) {
            return true;
        }

        return false;
    }

    /**
     * Crear software
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('software.create')) {
            return true;
        }

        return false;
    }

    /**
     * Actualizar software
     */
    public function update(User $user, Software $software): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('software.update')) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar software
     */
    public function delete(User $user, Software $software): bool
    {
        if ($user->hasRole('infrastructure')) {
            return true;
        }

        if ($user->hasPermissionTo('software.delete')) {
            return true;
        }

        return false;
    }
}
