<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Enrollment;
use App\Domains\Lms\Models\EnrollmentDetail;
use Illuminate\Database\Eloquent\Collection;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = Enrollment::with(['student', 'academicPeriod', 'enrollmentDetails.courseOffering']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['academic_period_id'])) {
            $query->where('academic_period_id', $filters['academic_period_id']);
        }

        return $query->orderBy('enrollment_date', 'desc')->get();
    }

    public function create(array $data): Enrollment
    {
        return Enrollment::create($data);
    }

    public function createDetail(int $enrollmentId, array $detailData): EnrollmentDetail
    {
        $detailData['enrollment_id'] = $enrollmentId;
        return EnrollmentDetail::create($detailData);
    }
}
