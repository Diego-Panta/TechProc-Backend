<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Course;
use App\Domains\Lms\Repositories\CourseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseService
{
    protected CourseRepositoryInterface $repository;

    public function __construct(CourseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all courses with filters and pagination
     */
    public function getAllCourses(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Find a course by ID
     */
    public function getCourseById(int $courseId): ?Course
    {
        return $this->repository->findById($courseId);
    }

    /**
     * Create a new course with categories and instructors
     */
    public function createCourse(array $data): Course
    {
        // Extraer relaciones antes de crear
        $categoryIds = $data['category_ids'] ?? [];
        $instructorIds = $data['instructor_ids'] ?? [];
        
        unset($data['category_ids'], $data['instructor_ids']);

        // Establecer valores por defecto
        $data['status'] = $data['status'] ?? 'activo';
        
        // Crear el curso
        $course = $this->repository->create($data);

        // Asignar course_id si no existe
        if (!$course->course_id) {
            $course->course_id = $course->id;
            $course->save();
        }

        // Sincronizar relaciones
        if (!empty($categoryIds)) {
            $this->repository->syncCategories($course, $categoryIds);
        }

        if (!empty($instructorIds)) {
            $this->repository->syncInstructors($course, $instructorIds);
        }

        return $course->fresh(['categories', 'instructors']);
    }

    /**
     * Update an existing course
     */
    public function updateCourse(int $courseId, array $data): Course
    {
        // Extraer relaciones antes de actualizar
        $categoryIds = $data['category_ids'] ?? null;
        $instructorIds = $data['instructor_ids'] ?? null;
        
        unset($data['category_ids'], $data['instructor_ids']);

        // Actualizar el curso
        $course = $this->repository->update($courseId, $data);

        // Sincronizar relaciones si se proporcionaron
        if ($categoryIds !== null) {
            $this->repository->syncCategories($course, $categoryIds);
        }

        if ($instructorIds !== null) {
            $this->repository->syncInstructors($course, $instructorIds);
        }

        return $course->fresh(['categories', 'instructors']);
    }

    /**
     * Delete a course and its relationships
     */
    public function deleteCourse(int $courseId): bool
    {
        $course = $this->repository->findById($courseId);
        
        if (!$course) {
            return false;
        }

        // Eliminar relaciones
        $this->repository->syncCategories($course, []);
        $this->repository->syncInstructors($course, []);
        
        // Eliminar contenidos del curso
        $course->courseContents()->delete();

        // Eliminar el curso
        return $this->repository->delete($courseId);
    }
}
