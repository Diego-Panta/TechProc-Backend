<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Course;
use App\Domains\Lms\Models\Group;
use App\Domains\Lms\Repositories\CourseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

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

        // Crear automáticamente un grupo para este curso
        $this->createDefaultGroup($course);

        return $course->fresh();
    }

    /**
     * Create a default group for a course
     */
    private function createDefaultGroup(Course $course): void
    {
        // Generar código único para el grupo
        $baseCode = 'GRP-' . str_pad($course->id, 4, '0', STR_PAD_LEFT);
        $code = $baseCode;
        $counter = 1;

        // Asegurar que el código sea único
        while (Group::where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter;
            $counter++;
        }

        // Determinar el nombre del grupo basado en los datos del curso
        $groupName = $course->name ?? $course->title ?? 'Grupo Principal';

        // Calcular fechas por defecto (30 días desde hoy)
        $startDate = now()->addDays(7); // Inicia en 7 días
        $endDate = $startDate->copy()->addDays(30); // Dura 30 días

        // Mapear el status del curso (boolean) al status del grupo (string)
        $groupStatus = $course->status ? 'draft' : 'cancelled';

        // Crear el grupo
        Group::create([
            'course_id' => $course->id,
            'code' => $code,
            'name' => $groupName,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => $groupStatus,
        ]);
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
