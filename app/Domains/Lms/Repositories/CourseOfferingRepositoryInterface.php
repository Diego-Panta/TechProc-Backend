<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\CourseOffering;
use Illuminate\Database\Eloquent\Collection;

interface CourseOfferingRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?CourseOffering;

    public function create(array $data): CourseOffering;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
