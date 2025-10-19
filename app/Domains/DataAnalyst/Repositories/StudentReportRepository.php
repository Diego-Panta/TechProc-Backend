<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Models\Company;
use App\Domains\Lms\Models\Enrollment;
use App\Domains\Lms\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Builder;

class StudentReportRepository
{
    public function getStudentsWithFilters(array $filters = [])
    {
        $query = Student::with(['company', 'enrollments.academicPeriod'])
            ->select('students.*')
            ->leftJoin('companies', 'students.company_id', '=', 'companies.id')
            ->withCount('enrollments');

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('students.first_name', 'ILIKE', "%{$search}%")
                    ->orWhere('students.last_name', 'ILIKE', "%{$search}%")
                    ->orWhere('students.email', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['company'])) {
            $query->where('companies.name', 'ILIKE', "%{$filters['company']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('students.status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('students.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('students.created_at', '<=', $filters['end_date']);
        }

        // Filtros adicionales para API
        if (!empty($filters['company_id'])) {
            $query->where('students.company_id', $filters['company_id']);
        }

        if (!empty($filters['min_enrollments'])) {
            $query->has('enrollments', '>=', $filters['min_enrollments']);
        }

        if (!empty($filters['max_enrollments'])) {
            $query->has('enrollments', '<=', $filters['max_enrollments']);
        }

        return $query->orderBy('students.created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getStudentDetail($studentId)
    {
        return Student::with([
            'company',
            'enrollments.academicPeriod',
            'enrollments.enrollmentDetails'
        ])->findOrFail($studentId);
    }

    public function getStudentStatistics(array $filters = [])
    {
        $baseQuery = Student::query();

        // Filtros de fecha
        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Filtro por empresa
        if (!empty($filters['company_id'])) {
            $baseQuery->where('company_id', $filters['company_id']);
        }

        $totalStudents = $baseQuery->count();
        $activeStudents = (clone $baseQuery)->where('status', 'active')->count();
        $inactiveStudents = (clone $baseQuery)->where('status', 'inactive')->count();

        // Estadísticas por empresa
        $byCompany = Student::join('companies', 'students.company_id', '=', 'companies.id')
            ->selectRaw('companies.id as company_id, companies.name as company_name, COUNT(*) as student_count')
            ->groupBy('companies.id', 'companies.name')
            ->when(!empty($filters['company_id']), function ($q) use ($filters) {
                $q->where('companies.id', $filters['company_id']);
            })
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('students.created_at', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('students.created_at', '<=', $filters['end_date']);
            })
            ->get();

        // Tendencia de matrículas
        $enrollmentQuery = Enrollment::join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('academic_periods', 'enrollments.academic_period_id', '=', 'academic_periods.id')
            ->selectRaw('academic_periods.name as period, COUNT(*) as enrolled');

        // Aplicar filtros a la tendencia de matrículas
        if (!empty($filters['academic_period_id'])) {
            $enrollmentQuery->where('academic_periods.id', $filters['academic_period_id']);
        }

        if (!empty($filters['company_id'])) {
            $enrollmentQuery->where('students.company_id', $filters['company_id']);
        }

        $enrollmentTrend = $enrollmentQuery->groupBy('academic_periods.id', 'academic_periods.name')
            ->orderBy('academic_periods.name')
            ->get();

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'inactive_students' => $inactiveStudents,
            'by_company' => $byCompany,
            'enrollment_trend' => $enrollmentTrend,
            'by_status' => [
                'active' => $activeStudents,
                'inactive' => $inactiveStudents,
            ]
        ];
    }
}
