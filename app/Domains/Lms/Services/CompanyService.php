<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Company;
use App\Domains\Lms\Repositories\CompanyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanyService
{
    protected CompanyRepositoryInterface $repository;

    public function __construct(CompanyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCompanies(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getCompanyById(int $id): ?Company
    {
        return $this->repository->findById($id);
    }

    public function createCompany(array $data): Company
    {
        return $this->repository->create($data);
    }

    public function updateCompany(int $id, array $data): ?Company
    {
        return $this->repository->update($id, $data);
    }

    public function deleteCompany(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
