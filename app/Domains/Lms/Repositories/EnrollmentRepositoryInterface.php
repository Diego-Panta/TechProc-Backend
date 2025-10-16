<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Enrollment;
use App\Domains\Lms\Models\EnrollmentDetail;
use Illuminate\Database\Eloquent\Collection;

interface EnrollmentRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function create(array $data): Enrollment;
    public function createDetail(int $enrollmentId, array $detailData): EnrollmentDetail;
}
