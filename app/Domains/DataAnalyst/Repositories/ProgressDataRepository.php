<?php
// app/Domains/DataAnalyst/Repositories/ProgressDataRepository.php

namespace App\Domains\DataAnalyst\Repositories;

use Illuminate\Support\Facades\DB;
use IncadevUns\CoreDomain\Enums\EnrollmentAcademicStatus;
use Illuminate\Support\Facades\Log;

class ProgressDataRepository
{
    /**
     * Obtiene datos de progreso del estudiante
     */
    public function getStudentProgressData(int $enrollmentId, string $period = '30d'): array
    {
        try {
            $dateRange = $this->getDateRange($period);

            return [
                'grades' => $this->getStudentGrades($enrollmentId, $dateRange),
                'modules' => $this->getCourseModules($enrollmentId),
                'completed_modules' => $this->getCompletedModules($enrollmentId, $dateRange),
            ];
        } catch (\Exception $e) {
            Log::error("Error getting progress data for enrollment {$enrollmentId}: " . $e->getMessage());
            return ['grades' => [], 'modules' => [], 'completed_modules' => []];
        }
    }

    /**
     * Obtiene calificaciones del estudiante
     */
    private function getStudentGrades(int $enrollmentId, array $dateRange): array
    {
        return DB::table('grades')
            ->select([
                'grades.grade',
                'grades.created_at',
                'exams.title as exam_title',
                'modules.id as module_id',
                'modules.title as module_title'
            ])
            ->join('exams', 'grades.exam_id', '=', 'exams.id')
            ->join('modules', 'exams.module_id', '=', 'modules.id')
            ->where('grades.enrollment_id', $enrollmentId)
            ->when($dateRange['start'], function ($query) use ($dateRange) {
                $query->whereBetween('grades.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->orderBy('grades.created_at')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene módulos del curso - CORREGIDO
     */
    private function getCourseModules(int $enrollmentId): array
    {
        return DB::table('enrollments as e')
            ->select([
                'm.id',
                'm.title',
                'm.sort',
                'g.start_date',
                'g.end_date'
            ])
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->join('modules as m', 'cv.id', '=', 'm.course_version_id')
            ->where('e.id', $enrollmentId)
            ->where('e.academic_status', EnrollmentAcademicStatus::Active->value)
            ->orderBy('m.sort')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene módulos completados - CORREGIDO (LÓGICA MEJORADA)
     */
    private function getCompletedModules(int $enrollmentId, array $dateRange): array
    {
        try {
            // Obtener todos los módulos del curso
            $courseModules = $this->getCourseModules($enrollmentId);

            if (empty($courseModules)) {
                return [];
            }

            // Obtener módulos en los que el estudiante ha realizado actividades (exámenes)
            $modulesWithActivity = DB::table('grades as g')
                ->select([
                    'm.id as module_id',
                    'm.title as module_title',
                    DB::raw('MAX(g.grade) as best_grade'),
                    DB::raw('COUNT(g.id) as exams_count'),
                    DB::raw('MAX(g.created_at) as last_exam_date'),
                    DB::raw('AVG(g.grade) as average_grade')
                ])
                ->join('exams as e', 'g.exam_id', '=', 'e.id')
                ->join('modules as m', 'e.module_id', '=', 'm.id')
                ->where('g.enrollment_id', $enrollmentId)
                ->when($dateRange['start'], function ($query) use ($dateRange) {
                    $query->whereBetween('g.created_at', [$dateRange['start'], $dateRange['end']]);
                })
                ->groupBy('m.id', 'm.title')
                ->get()
                ->keyBy('module_id');

            Log::info("Modules with activity for enrollment {$enrollmentId}:", [
                'total_course_modules' => count($courseModules),
                'modules_with_activity' => $modulesWithActivity->count(),
                'modules_details' => $modulesWithActivity->toArray()
            ]);

            // Un módulo se considera "completado" si el estudiante ha realizado al menos una actividad en él
            // Independientemente de si aprobó o no
            $completedModules = [];
            foreach ($courseModules as $module) {
                $moduleActivity = $modulesWithActivity[$module->id] ?? null;

                if ($moduleActivity) {
                    $completedModules[] = (object) [
                        'module_id' => $module->id,
                        'module_title' => $module->title,
                        'best_grade' => $moduleActivity->best_grade,
                        'average_grade' => $moduleActivity->average_grade,
                        'exams_count' => $moduleActivity->exams_count,
                        'last_exam_date' => $moduleActivity->last_exam_date,
                        'completed_at' => $moduleActivity->last_exam_date,
                        'is_approved' => $moduleActivity->best_grade >= 11, // Información adicional
                        'status' => $moduleActivity->best_grade >= 11 ? 'approved' : 'failed'
                    ];
                }
            }

            Log::info("Completed modules (new criteria) for enrollment {$enrollmentId}:", [
                'completed_count' => count($completedModules),
                'completed_modules' => $completedModules
            ]);

            return $completedModules;
        } catch (\Exception $e) {
            Log::error("Error getting completed modules for enrollment {$enrollmentId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene información de la matrícula
     */
    public function getEnrollment(int $enrollmentId)
    {
        return DB::table('enrollments')
            ->where('id', $enrollmentId)
            ->where('academic_status', EnrollmentAcademicStatus::Active->value)
            ->first();
    }

    /**
     * Obtiene promedio de progreso del grupo - CORREGIDO
     */
    public function getGroupProgressAverage(int $groupId): array
    {
        try {
            $result = DB::table('enrollments as e')
                ->selectRaw('
                    AVG(g.grade) as average_grade,
                    COUNT(DISTINCT e.id) as total_students
                ')
                ->leftJoin('grades as g', 'e.id', '=', 'g.enrollment_id')
                ->where('e.group_id', $groupId)
                ->where('e.academic_status', EnrollmentAcademicStatus::Active->value)
                ->first();

            // Calcular promedio normalizado (0-100)
            $averageGrade = $result->average_grade ?? 0;
            $averageGradeNormalized = $averageGrade > 0 ? ($averageGrade / 20) * 100 : 0;

            return [
                'average_grade' => round($averageGrade, 2),
                'average_grade_normalized' => round($averageGradeNormalized, 2),
                'completion_rate' => $this->calculateGroupCompletionRate($groupId),
                'total_students' => $result->total_students ?? 0
            ];
        } catch (\Exception $e) {
            Log::error("Error getting group progress average for group {$groupId}: " . $e->getMessage());
            return [
                'average_grade' => 0,
                'average_grade_normalized' => 0,
                'completion_rate' => 0,
                'total_students' => 0
            ];
        }
    }

    /**
     * Calcula tasa de completación del grupo - CORREGIDO
     */
    private function calculateGroupCompletionRate(int $groupId): float
    {
        try {
            // Obtener todos los módulos del grupo
            $totalModules = DB::table('groups as g')
                ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
                ->join('modules as m', 'cv.id', '=', 'm.course_version_id')
                ->where('g.id', $groupId)
                ->count();

            if ($totalModules === 0) {
                return 0.0;
            }

            // Obtener estudiantes activos del grupo
            $activeStudents = DB::table('enrollments')
                ->where('group_id', $groupId)
                ->where('academic_status', EnrollmentAcademicStatus::Active->value)
                ->pluck('id');

            if ($activeStudents->isEmpty()) {
                return 0.0;
            }

            // Calcular módulos completados por estudiante
            $completedData = DB::table('grades as g')
                ->selectRaw('
                    g.enrollment_id,
                    COUNT(DISTINCT e.module_id) as completed_modules
                ')
                ->join('exams as e', 'g.exam_id', '=', 'e.id')
                ->whereIn('g.enrollment_id', $activeStudents)
                ->where('g.grade', '>=', 11) // Aprobado en escala 0-20
                ->groupBy('g.enrollment_id')
                ->get();

            $totalCompleted = $completedData->sum('completed_modules');
            $totalPossible = $activeStudents->count() * $totalModules;

            if ($totalPossible === 0) {
                return 0.0;
            }

            $completionRate = ($totalCompleted / $totalPossible) * 100;
            return round($completionRate, 2);
        } catch (\Exception $e) {
            Log::error("Error calculating group completion rate for group {$groupId}: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Define el rango de fechas
     */
    private function getDateRange(string $period): array
    {
        return match ($period) {
            '7d' => ['start' => now()->subDays(7), 'end' => now()],
            '30d' => ['start' => now()->subDays(30), 'end' => now()],
            '90d' => ['start' => now()->subDays(90), 'end' => now()],
            'all' => ['start' => null, 'end' => null], // Sin filtro de fecha
            default => ['start' => now()->subDays(30), 'end' => now()],
        };
    }
}
