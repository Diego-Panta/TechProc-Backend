<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CompanyRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?Company;

    public function create(array $data): Company;

    public function update(int $id, array $data): ?Company;

    public function delete(int $id): bool;
}
