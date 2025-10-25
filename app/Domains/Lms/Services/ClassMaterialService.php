<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\ClassMaterial;
use App\Domains\Lms\Repositories\ClassMaterialRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassMaterialService
{
    protected ClassMaterialRepositoryInterface $repository;

    public function __construct(ClassMaterialRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all class materials with filters and pagination
     */
    public function getAllMaterials(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Find a class material by ID
     */
    public function getMaterialById(int $materialId): ?ClassMaterial
    {
        return $this->repository->findById($materialId);
    }

    /**
     * Create a new class material
     */
    public function createMaterial(array $data): ClassMaterial
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing class material
     */
    public function updateMaterial(int $materialId, array $data): ?ClassMaterial
    {
        $material = $this->repository->findById($materialId);

        if (!$material) {
            return null;
        }

        return $this->repository->update($materialId, $data);
    }

    /**
     * Delete a class material
     */
    public function deleteMaterial(int $materialId): bool
    {
        $material = $this->repository->findById($materialId);

        if (!$material) {
            return false;
        }

        return $this->repository->delete($materialId);
    }
}
