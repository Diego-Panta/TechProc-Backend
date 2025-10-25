<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\ClassModel;
use App\Domains\Lms\Repositories\ClassRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassService
{
    protected ClassRepositoryInterface $repository;

    public function __construct(ClassRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all classes with filters and pagination
     */
    public function getAllClasses(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Find a class by ID
     */
    public function getClassById(int $classId): ?ClassModel
    {
        return $this->repository->findById($classId);
    }

    /**
     * Create a new class
     */
    public function createClass(array $data): ClassModel
    {
        // Establecer valores por defecto
        $data['class_status'] = $data['class_status'] ?? 'SCHEDULED';

        // Crear la clase
        return $this->repository->create($data);
    }

    /**
     * Update an existing class
     */
    public function updateClass(int $classId, array $data): ?ClassModel
    {
        $class = $this->repository->findById($classId);

        if (!$class) {
            return null;
        }

        // Actualizar la clase
        return $this->repository->update($classId, $data);
    }

    /**
     * Delete a class
     */
    public function deleteClass(int $classId): bool
    {
        $class = $this->repository->findById($classId);

        if (!$class) {
            return false;
        }

        // Eliminar la clase
        return $this->repository->delete($classId);
    }
}
