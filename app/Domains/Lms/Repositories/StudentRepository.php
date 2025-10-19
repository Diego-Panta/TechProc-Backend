<?php

namespace App\Domains\Lms\Repositories;

use App\Domains\Lms\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentRepository implements StudentRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Student::with('company');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('document_number', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $studentId): ?Student
    {
        return Student::with(['company', 'enrollments.enrollmentDetails.courseOffering'])
            ->where('student_id', $studentId)
            ->orWhere('id', $studentId)
            ->first();
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }

    public function update(int $studentId, array $data): Student
    {
        $student = Student::where('student_id', $studentId)
            ->orWhere('id', $studentId)
            ->firstOrFail();
        
        $student->update($data);
        
        return $student->fresh(['company']);
    }

    public function delete(int $studentId): bool
    {
        $student = Student::where('student_id', $studentId)
            ->orWhere('id', $studentId)
            ->firstOrFail();
        
        return $student->delete();
    }
}
