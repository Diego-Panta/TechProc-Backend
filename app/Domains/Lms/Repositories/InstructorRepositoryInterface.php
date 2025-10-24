<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Instructor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InstructorRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findById(int $instructorId): ?Instructor;
    public function create(array $data): Instructor;
    public function update(int $instructorId, array $data): Instructor;
}
