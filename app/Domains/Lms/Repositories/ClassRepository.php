<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\ClassModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassRepository implements ClassRepositoryInterface
{
    /**
     * Get all classes with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = ClassModel::with('group.course');

        // Filtro por group_id
        if (isset($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }

        // Filtro por class_status
        if (isset($filters['class_status'])) {
            $query->where('class_status', $filters['class_status']);
        }

        // Búsqueda por nombre de clase
        if (isset($filters['search'])) {
            $query->where('class_name', 'LIKE', "%{$filters['search']}%");
        }

        // Filtro por rango de fechas
        if (isset($filters['class_date_from'])) {
            $query->where('class_date', '>=', $filters['class_date_from']);
        }

        if (isset($filters['class_date_to'])) {
            $query->where('class_date', '<=', $filters['class_date_to']);
        }

        return $query->orderBy('class_date', 'asc')
                     ->orderBy('start_time', 'asc')
                     ->paginate($perPage);
    }

    /**
     * Find a class by ID
     */
    public function findById(int $classId): ?ClassModel
    {
        return ClassModel::with('group.course')->find($classId);
    }

    /**
     * Create a new class
     */
    public function create(array $data): ClassModel
    {
        return ClassModel::create($data);
    }

    /**
     * Update an existing class
     */
    public function update(int $classId, array $data): ClassModel
    {
        $class = ClassModel::findOrFail($classId);
        $class->update($data);

        return $class->fresh();
    }

    /**
     * Delete a class
     */
    public function delete(int $classId): bool
    {
        $class = ClassModel::findOrFail($classId);
        return $class->delete();
    }
}
