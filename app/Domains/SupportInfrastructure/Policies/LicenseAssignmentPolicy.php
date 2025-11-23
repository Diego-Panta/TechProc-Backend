<?php

namespace App\Domains\SupportInfrastructure\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\LicenseAssignment;

class LicenseAssignmentPolicy
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
     * Ver lista de asignaciones
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('license-assignments.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver una asignación específica
     */
    public function view(User $user, LicenseAssignment $assignment): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('license-assignments.view')) {
            return true;
        }

        return false;
    }

    /**
     * Crear asignaciones
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('license-assignments.create')) {
            return true;
        }

        return false;
    }

    /**
     * Actualizar asignaciones
     */
    public function update(User $user, LicenseAssignment $assignment): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('license-assignments.update')) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar asignaciones
     */
    public function delete(User $user, LicenseAssignment $assignment): bool
    {
        if ($user->hasRole('infrastructure')) {
            return true;
        }

        if ($user->hasPermissionTo('license-assignments.delete')) {
            return true;
        }

        return false;
    }
}
