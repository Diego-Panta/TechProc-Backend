<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\Lms\Models\GradeRecord;
use App\Domains\Lms\Models\FinalGrade;
use App\Domains\Lms\Models\Group;
use App\Domains\Lms\Models\Course;
use App\Domains\Administrator\Models\User;
use App\Domains\Lms\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Domains\Lms\Models\AcademicPeriod;

class GradeReportRepository
{
    public function getGradeStatistics(array $filters = [])
    {
        // Estadísticas básicas de calificaciones
        $gradeQuery = GradeRecord::query();
        
        // Aplicar filtros
        $this->applyGradeFilters($gradeQuery, $filters);
        
        $totalGrades = $gradeQuery->count();
        $averageGrade = $gradeQuery->avg('obtained_grade');
        
        // Tasa de aprobación (60 como nota mínima para aprobar en escala 0-100)
        $passingCount = (clone $gradeQuery)->where('obtained_grade', '>=', 60)->count();
        $passingRate = $totalGrades > 0 ? ($passingCount / $totalGrades) * 100 : 0;

        // CORREGIDO: Estadísticas por grupo - usar la relación a través de evaluation
        $byGroup = Group::select(
                'groups.id as group_id',
                'groups.name as group_name',
                'courses.name as course_name',
                DB::raw('COUNT(grade_records.id) as total_grades'),
                DB::raw('AVG(grade_records.obtained_grade) as average_grade'),
                DB::raw('(COUNT(CASE WHEN grade_records.obtained_grade >= 60 THEN 1 END) * 100.0 / COUNT(grade_records.id)) as passing_rate')
            )
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->leftJoin('evaluations', 'groups.id', '=', 'evaluations.group_id')
            ->leftJoin('grade_records', 'evaluations.id', '=', 'grade_records.evaluation_id')
            ->when(!empty($filters['course_id']), function ($q) use ($filters) {
                $q->where('groups.course_id', $filters['course_id']);
            })
            ->when(!empty($filters['academic_period_id']), function ($q) use ($filters) {
                $q->whereHas('course.courseOfferings', function ($subQuery) use ($filters) {
                    $subQuery->where('academic_period_id', $filters['academic_period_id']);
                });
            })
            ->groupBy('groups.id', 'groups.name', 'courses.name')
            ->havingRaw('COUNT(grade_records.id) > 0')
            ->get();

        // Mejores estudiantes
        $topPerformers = $this->getTopPerformersData($filters);

        return [
            'total_grades_recorded' => $totalGrades,
            'average_grade' => round($averageGrade ?? 0, 1),
            'passing_rate' => round($passingRate, 1),
            'by_group' => $byGroup,
            'top_performers' => $topPerformers
        ];
    }

    public function getTopPerformers(array $filters = [], $limit = 10)
    {
        return $this->getTopPerformersData($filters, $limit);
    }

    public function getGradeReport(array $filters = [])
    {
        $query = GradeRecord::with([
            'user.student',
            'evaluation.group.course', // CORREGIDO: Acceder al grupo a través de evaluation
            'evaluation'
        ]);

        $this->applyGradeFilters($query, $filters);

        return $query->orderBy('grade_records.record_date', 'desc')
                    ->paginate($filters['limit'] ?? 15);
    }

    private function applyGradeFilters(Builder $query, array $filters)
    {
        if (!empty($filters['course_id'])) {
            $query->whereHas('evaluation.group', function ($q) use ($filters) {
                $q->where('course_id', $filters['course_id']);
            });
        }

        if (!empty($filters['academic_period_id'])) {
            $query->whereHas('evaluation.group.course.courseOfferings', function ($q) use ($filters) {
                $q->where('academic_period_id', $filters['academic_period_id']);
            });
        }

        if (!empty($filters['grade_type'])) {
            // CORREGIDO: Ya no existe grade_type en grade_records, usar evaluation_type
            $query->whereHas('evaluation', function ($q) use ($filters) {
                $q->where('evaluation_type', $filters['grade_type']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('grade_records.record_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('grade_records.record_date', '<=', $filters['end_date']);
        }
    }

    private function getTopPerformersData(array $filters = [], $limit = 10)
    {
        $query = User::select(
                'users.id as user_id',
                'students.first_name',
                'students.last_name',
                DB::raw('AVG(grade_records.obtained_grade) as average_grade'),
                DB::raw('COUNT(grade_records.id) as total_grades')
            )
            ->join('students', 'users.id', '=', 'students.user_id')
            ->join('grade_records', 'users.id', '=', 'grade_records.user_id')
            ->join('evaluations', 'grade_records.evaluation_id', '=', 'evaluations.id') // CORREGIDO
            ->join('groups', 'evaluations.group_id', '=', 'groups.id') // CORREGIDO
            ->where('grade_records.obtained_grade', '>=', 60)
            ->groupBy('users.id', 'students.first_name', 'students.last_name')
            ->havingRaw('COUNT(grade_records.id) >= 3')
            ->orderBy('average_grade', 'desc')
            ->limit($limit);

        if (!empty($filters['course_id'])) {
            $query->where('groups.course_id', $filters['course_id']);
        }

        if (!empty($filters['academic_period_id'])) {
            $query->whereHas('gradeRecords.evaluation.group.course.courseOfferings', function ($q) use ($filters) {
                $q->where('academic_period_id', $filters['academic_period_id']);
            });
        }

        return $query->get();
    }

    public function getAvailableCourses()
    {
        return Course::select('id', 'name', 'title')
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    public function getAvailableAcademicPeriods()
    {
        return AcademicPeriod::select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}