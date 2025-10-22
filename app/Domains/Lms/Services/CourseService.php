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
     * Create a new course
     */
    public function createCourse(array $data): Course
    {
        // Establecer valores por defecto
        $data['status'] = $data['status'] ?? true;

        // Crear el curso
        $course = $this->repository->create($data);

        // Asignar course_id si no existe
        if (!$course->course_id) {
            $course->course_id = $course->id;
            $course->save();
        }

        return $course->fresh();
    }

    /**
     * Update an existing course
     */
    public function updateCourse(int $courseId, array $data): Course
    {
        // Actualizar el curso
        $course = $this->repository->update($courseId, $data);

        return $course->fresh();
    }

    /**
     * Delete a course
     */
    public function deleteCourse(int $courseId): bool
    {
        $course = $this->repository->findById($courseId);

        if (!$course) {
            return false;
        }

        // Eliminar el curso
        return $this->repository->delete($courseId);
    }
}
