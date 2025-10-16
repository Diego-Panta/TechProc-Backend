<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findById(int $studentId): ?Student;
    public function create(array $data): Student;
    public function update(int $studentId, array $data): Student;
    public function delete(int $studentId): bool;
}
