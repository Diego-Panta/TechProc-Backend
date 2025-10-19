<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Collection;

class AcademicPeriodRepository implements AcademicPeriodRepositoryInterface
{
    public function getAll(): Collection
    {
        return AcademicPeriod::orderBy('start_date', 'desc')->get();
    }

    public function findById(int $id): ?AcademicPeriod
    {
        return AcademicPeriod::find($id);
    }

    public function create(array $data): AcademicPeriod
    {
        return AcademicPeriod::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $academicPeriod = AcademicPeriod::find($id);

        if (!$academicPeriod) {
            return false;
        }

        return $academicPeriod->update($data);
    }

    public function delete(int $id): bool
    {
        $academicPeriod = AcademicPeriod::find($id);

        if (!$academicPeriod) {
            return false;
        }

        return $academicPeriod->delete();
    }
}
