<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\CourseOffering;
use App\Domains\Lms\Repositories\CourseOfferingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseOfferingService
{
    protected CourseOfferingRepositoryInterface $repository;

    public function __construct(CourseOfferingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCourseOfferings(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get course offerings from the latest published academic period
     */
    public function getCourseOfferingsFromLatestPeriod(): Collection
    {
        // Obtener el último período académico con status 'open'
        $latestPeriod = \App\Domains\Lms\Models\AcademicPeriod::where('status', 'open')
            ->orderBy('start_date', 'desc')
            ->first();

        // Si no hay período abierto, retornar colección vacía
        if (!$latestPeriod) {
            return new Collection();
        }

        // Obtener todos los course offerings de ese período
        return CourseOffering::with(['course', 'academicPeriod', 'instructor.user'])
            ->where('academic_period_id', $latestPeriod->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCourseOfferingById(int $id): ?CourseOffering
    {
        return $this->repository->findById($id);
    }

    public function createCourseOffering(array $data): CourseOffering
    {
        return $this->repository->create($data);
    }

    public function updateCourseOffering(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteCourseOffering(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
