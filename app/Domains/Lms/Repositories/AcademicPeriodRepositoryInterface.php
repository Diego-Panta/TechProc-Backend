<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Collection;

interface AcademicPeriodRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?AcademicPeriod;

    public function create(array $data): AcademicPeriod;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
