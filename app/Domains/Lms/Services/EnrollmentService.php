<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Enrollment;
use App\Domains\Lms\Repositories\EnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    protected EnrollmentRepositoryInterface $repository;

    public function __construct(EnrollmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEnrollments(array $filters): Collection
    {
        return $this->repository->getAll($filters);
    }

    public function createEnrollment(array $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // Extraer course_offering_ids antes de crear enrollment
            $courseOfferingIds = $data['course_offering_ids'] ?? [];
            unset($data['course_offering_ids']);

            // Crear enrollment
            $enrollment = $this->repository->create($data);

            // Asignar enrollment_id si no existe
            if (!$enrollment->enrollment_id) {
                $enrollment->enrollment_id = $enrollment->id;
                $enrollment->save();
            }

            // Crear detalles de matrícula (enrollment_details)
            foreach ($courseOfferingIds as $offeringId) {
                $this->repository->createDetail($enrollment->id, [
                    'course_offering_id' => $offeringId,
                    'status' => 'active',
                    'created_at' => now(),
                ]);
            }

            return $enrollment->fresh(['student', 'academicPeriod', 'enrollmentDetails.courseOffering']);
        });
    }
}
