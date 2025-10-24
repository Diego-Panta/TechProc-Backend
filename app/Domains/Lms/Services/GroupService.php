<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Group;
use App\Domains\Lms\Repositories\GroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GroupService
{
    protected GroupRepositoryInterface $repository;

    public function __construct(GroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all groups with filters and pagination
     */
    public function getAllGroups(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Find a group by ID
     */
    public function getGroupById(int $groupId): ?Group
    {
        return $this->repository->findById($groupId);
    }

    /**
     * Create a new group
     */
    public function createGroup(array $data): Group
    {
        // Establecer valores por defecto
        $data['status'] = $data['status'] ?? 'draft';

        // Crear el grupo
        return $this->repository->create($data);
    }

    /**
     * Update an existing group
     */
    public function updateGroup(int $groupId, array $data): ?Group
    {
        $group = $this->repository->findById($groupId);

        if (!$group) {
            return null;
        }

        // Actualizar el grupo
        return $this->repository->update($groupId, $data);
    }

    /**
     * Delete a group
     */
    public function deleteGroup(int $groupId): bool
    {
        $group = $this->repository->findById($groupId);

        if (!$group) {
            return false;
        }

        // Eliminar el grupo
        return $this->repository->delete($groupId);
    }
}
