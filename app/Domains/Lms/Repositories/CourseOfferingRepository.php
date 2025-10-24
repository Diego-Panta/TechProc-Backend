<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\CourseOffering;
use Illuminate\Database\Eloquent\Collection;

class CourseOfferingRepository implements CourseOfferingRepositoryInterface
{
    public function getAll(): Collection
    {
        return CourseOffering::with(['course', 'academicPeriod', 'instructor.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById(int $id): ?CourseOffering
    {
        return CourseOffering::with(['course', 'academicPeriod', 'instructor.user'])->find($id);
    }

    public function create(array $data): CourseOffering
    {
        return CourseOffering::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $courseOffering = CourseOffering::find($id);

        if (!$courseOffering) {
            return false;
        }

        return $courseOffering->update($data);
    }

    public function delete(int $id): bool
    {
        $courseOffering = CourseOffering::find($id);

        if (!$courseOffering) {
            return false;
        }

        return $courseOffering->delete();
    }
}
