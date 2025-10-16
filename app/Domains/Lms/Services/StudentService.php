<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Repositories\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentService
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllStudents(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getStudentById(int $studentId): ?Student
    {
        return $this->repository->findById($studentId);
    }

    public function createStudent(array $data): Student
    {
        $data['status'] = $data['status'] ?? 'activo';
        
        $student = $this->repository->create($data);

        if (!$student->student_id) {
            $student->student_id = $student->id;
            $student->save();
        }

        return $student->fresh(['company']);
    }

    public function updateStudent(int $studentId, array $data): Student
    {
        return $this->repository->update($studentId, $data);
    }

    public function deleteStudent(int $studentId): bool
    {
        return $this->repository->delete($studentId);
    }
}
