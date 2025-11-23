<?php

namespace App\Domains\SupportInfrastructure\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\Hardware;

class HardwarePolicy
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
     * Ver lista de hardware
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('hardware.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver un hardware específico
     */
    public function view(User $user, Hardware $hardware): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('hardware.view')) {
            return true;
        }

        return false;
    }

    /**
     * Crear hardware
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('hardware.create')) {
            return true;
        }

        return false;
    }

    /**
     * Actualizar hardware
     */
    public function update(User $user, Hardware $hardware): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('hardware.update')) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar hardware
     */
    public function delete(User $user, Hardware $hardware): bool
    {
        if ($user->hasRole('infrastructure')) {
            return true;
        }

        if ($user->hasPermissionTo('hardware.delete')) {
            return true;
        }

        return false;
    }
}
