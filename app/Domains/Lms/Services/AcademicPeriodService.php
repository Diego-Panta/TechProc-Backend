<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\AcademicPeriod;
use App\Domains\Lms\Repositories\AcademicPeriodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AcademicPeriodService
{
    protected AcademicPeriodRepositoryInterface $repository;

    public function __construct(AcademicPeriodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllAcademicPeriods(): Collection
    {
        return $this->repository->getAll();
    }

    public function getAcademicPeriodById(int $id): ?AcademicPeriod
    {
        return $this->repository->findById($id);
    }

    public function createAcademicPeriod(array $data): AcademicPeriod
    {
        return $this->repository->create($data);
    }

    public function updateAcademicPeriod(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteAcademicPeriod(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
