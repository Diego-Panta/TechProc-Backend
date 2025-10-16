<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Instructor;
use App\Domains\Lms\Repositories\InstructorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstructorService
{
    protected InstructorRepositoryInterface $repository;

    public function __construct(InstructorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllInstructors(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getInstructorById(int $instructorId): ?Instructor
    {
        return $this->repository->findById($instructorId);
    }

    public function createInstructor(array $data): Instructor
    {
        $data['status'] = $data['status'] ?? 'activo';
        
        $instructor = $this->repository->create($data);

        if (!$instructor->instructor_id) {
            $instructor->instructor_id = $instructor->id;
            $instructor->save();
        }

        return $instructor->fresh(['user']);
    }

    public function updateInstructor(int $instructorId, array $data): Instructor
    {
        return $this->repository->update($instructorId, $data);
    }
}
