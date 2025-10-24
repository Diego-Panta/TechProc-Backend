<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\ClassMaterial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClassMaterialRepositoryInterface
{
    /**
     * Get all class materials with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a class material by ID
     */
    public function findById(int $materialId): ?ClassMaterial;

    /**
     * Create a new class material
     */
    public function create(array $data): ClassMaterial;

    /**
     * Update an existing class material
     */
    public function update(int $materialId, array $data): ClassMaterial;

    /**
     * Delete a class material
     */
    public function delete(int $materialId): bool;
}
