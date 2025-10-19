<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    /**
     * Get all courses with filters
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a course by ID with relationships
     */
    public function findById(int $courseId): ?Course;

    /**
     * Create a new course
     */
    public function create(array $data): Course;

    /**
     * Update an existing course
     */
    public function update(int $courseId, array $data): Course;

    /**
     * Delete a course
     */
    public function delete(int $courseId): bool;

    /**
     * Sync course categories
     */
    public function syncCategories(Course $course, array $categoryIds): void;

    /**
     * Sync course instructors
     */
    public function syncInstructors(Course $course, array $instructorIds): void;
}
