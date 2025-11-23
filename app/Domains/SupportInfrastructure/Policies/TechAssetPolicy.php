<?php

namespace App\Domains\SupportInfrastructure\Policies;

use App\Models\User;
use IncadevUns\CoreDomain\Models\TechAsset;

class TechAssetPolicy
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
     * Ver lista de activos
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('tech-assets.view')) {
            return true;
        }

        return false;
    }

    /**
     * Ver un activo específico
     */
    public function view(User $user, TechAsset $asset): bool
    {
        // Puede ver si es el usuario asignado
        if ($asset->user_id === $user->id) {
            return true;
        }

        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('tech-assets.view')) {
            return true;
        }

        return false;
    }

    /**
     * Crear activos
     */
    public function create(User $user): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('tech-assets.create')) {
            return true;
        }

        return false;
    }

    /**
     * Actualizar activos
     */
    public function update(User $user, TechAsset $asset): bool
    {
        if ($user->hasRole(['infrastructure', 'support'])) {
            return true;
        }

        if ($user->hasPermissionTo('tech-assets.update')) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar activos
     */
    public function delete(User $user, TechAsset $asset): bool
    {
        if ($user->hasRole('infrastructure')) {
            return true;
        }

        if ($user->hasPermissionTo('tech-assets.delete')) {
            return true;
        }

        return false;
    }
}
