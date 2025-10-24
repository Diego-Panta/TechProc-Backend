<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\ClassModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClassRepositoryInterface
{
    /**
     * Get all classes with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a class by ID
     */
    public function findById(int $classId): ?ClassModel;

    /**
     * Create a new class
     */
    public function create(array $data): ClassModel;

    /**
     * Update an existing class
     */
    public function update(int $classId, array $data): ClassModel;

    /**
     * Delete a class
     */
    public function delete(int $classId): bool;
}
