<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository implements CourseRepositoryInterface
{
    /**
     * Get all courses with filters
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Course::query();

        // Filtro por nivel
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Filtro por estado
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Búsqueda por título o nombre
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('name', 'LIKE', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find a course by ID
     */
    public function findById(int $courseId): ?Course
    {
        return Course::where('course_id', $courseId)
            ->orWhere('id', $courseId)
            ->first();
    }

    /**
     * Create a new course
     */
    public function create(array $data): Course
    {
        return Course::create($data);
    }

    /**
     * Update an existing course
     */
    public function update(int $courseId, array $data): Course
    {
        $course = Course::where('course_id', $courseId)
            ->orWhere('id', $courseId)
            ->firstOrFail();
        
        $course->update($data);
        
        return $course->fresh();
    }

    /**
     * Delete a course
     */
    public function delete(int $courseId): bool
    {
        $course = Course::where('course_id', $courseId)
            ->orWhere('id', $courseId)
            ->firstOrFail();

        return $course->delete();
    }
}
