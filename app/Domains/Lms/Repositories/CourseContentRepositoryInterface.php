<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\CourseContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CourseContentRepositoryInterface
{
    /**
     * Get all course contents with filters
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a course content by ID
     */
    public function findById(int $contentId): ?CourseContent;

    /**
     * Create a new course content
     */
    public function create(array $data): CourseContent;

    /**
     * Update an existing course content
     */
    public function update(int $contentId, array $data): CourseContent;

    /**
     * Delete a course content
     */
    public function delete(int $contentId): bool;
}
