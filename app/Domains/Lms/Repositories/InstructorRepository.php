<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Instructor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstructorRepository implements InstructorRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Instructor::with('user')->withCount('courses');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['expertise_area'])) {
            $query->where('expertise_area', 'LIKE', "%{$filters['expertise_area']}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $instructorId): ?Instructor
    {
        return Instructor::with('user')
            ->where('instructor_id', $instructorId)
            ->orWhere('id', $instructorId)
            ->first();
    }

    public function create(array $data): Instructor
    {
        return Instructor::create($data);
    }

    public function update(int $instructorId, array $data): Instructor
    {
        $instructor = Instructor::where('instructor_id', $instructorId)
            ->orWhere('id', $instructorId)
            ->firstOrFail();
        
        $instructor->update($data);
        
        return $instructor->fresh(['user']);
    }
}
