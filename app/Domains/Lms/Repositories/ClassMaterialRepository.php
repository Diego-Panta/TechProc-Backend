<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\ClassMaterial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassMaterialRepository implements ClassMaterialRepositoryInterface
{
    /**
     * Get all class materials with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = ClassMaterial::with('class.group.course');

        // Filtro por class_id
        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        // Filtro por type
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Búsqueda por URL
        if (isset($filters['search'])) {
            $query->where('material_url', 'LIKE', "%{$filters['search']}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find a class material by ID
     */
    public function findById(int $materialId): ?ClassMaterial
    {
        return ClassMaterial::with('class.group.course')->find($materialId);
    }

    /**
     * Create a new class material
     */
    public function create(array $data): ClassMaterial
    {
        return ClassMaterial::create($data);
    }

    /**
     * Update an existing class material
     */
    public function update(int $materialId, array $data): ClassMaterial
    {
        $material = ClassMaterial::findOrFail($materialId);
        $material->update($data);

        return $material->fresh();
    }

    /**
     * Delete a class material
     */
    public function delete(int $materialId): bool
    {
        $material = ClassMaterial::findOrFail($materialId);
        return $material->delete();
    }
}
