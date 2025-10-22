<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\CourseContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseContentRepository implements CourseContentRepositoryInterface
{
    /**
     * Get all course contents with filters
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = CourseContent::query()->with('course:id,course_id,title');

        // Filtro por curso
        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        // Filtro por tipo
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filtro por sesión
        if (isset($filters['session'])) {
            $query->where('session', $filters['session']);
        }

        // Búsqueda por título
        if (isset($filters['search'])) {
            $query->where('title', 'LIKE', "%{$filters['search']}%");
        }

        return $query->orderBy('order_number', 'asc')
                     ->orderBy('created_at', 'asc')
                     ->paginate($perPage);
    }

    /**
     * Find a course content by ID
     */
    public function findById(int $contentId): ?CourseContent
    {
        return CourseContent::with('course:id,course_id,title')
            ->where('id', $contentId)
            ->first();
    }

    /**
     * Create a new course content
     */
    public function create(array $data): CourseContent
    {
        return CourseContent::create($data);
    }

    /**
     * Update an existing course content
     */
    public function update(int $contentId, array $data): CourseContent
    {
        $content = CourseContent::findOrFail($contentId);
        $content->update($data);

        return $content->fresh();
    }

    /**
     * Delete a course content
     */
    public function delete(int $contentId): bool
    {
        $content = CourseContent::findOrFail($contentId);
        return $content->delete();
    }
}
