<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\CourseOffering;
use App\Domains\Lms\Repositories\CourseOfferingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseOfferingService
{
    protected CourseOfferingRepositoryInterface $repository;

    public function __construct(CourseOfferingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCourseOfferings(): Collection
    {
        return $this->repository->getAll();
    }

    public function getCourseOfferingById(int $id): ?CourseOffering
    {
        return $this->repository->findById($id);
    }

    public function createCourseOffering(array $data): CourseOffering
    {
        return $this->repository->create($data);
    }

    public function updateCourseOffering(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteCourseOffering(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
