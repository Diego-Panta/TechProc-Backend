<?php
// app/Domains/DataAnalyst/Services/LocalDropoutDatasetService.php

namespace App\Domains\DataAnalyst\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LocalDropoutDatasetService
{
    private $studentProfiles = [];
    private $coursePatterns = [];

    public function __construct()
    {
        $this->initializeStudentProfiles();
        $this->initializeCoursePatterns();
    }

    /**
     * Genera dataset histórico con hasta 1000+ registros
     */
    public function generateExtendedHistoricalDataset(int $numRecords = 1000): array
    {
        $historicalData = [];
        $baseStudents = $this->getBaseStudentData();

        if (empty($baseStudents)) {
            return $this->generateSyntheticDataset($numRecords);
        }

        // Para muchos registros, usar más versiones por estudiante
        $recordsPerStudent = max(5, ceil($numRecords / count($baseStudents)));
        
        foreach ($baseStudents as $student) {
            for ($i = 0; $i < $recordsPerStudent && count($historicalData) < $numRecords; $i++) {
                $historicalData[] = $this->createHistoricalRecord($student, $i, count($historicalData));
            }
        }

        return $historicalData;
    }

    /**
     * Crea registro histórico con SOLO 4 variables objetivo principales
     */
    private function createHistoricalRecord(object $baseStudent, int $version, int $recordId): array
    {
        $profileType = $this->determineStudentProfile($baseStudent);
        $profile = $this->studentProfiles[$profileType];
        $coursePattern = $this->getCoursePattern($baseStudent->course_name);

        $characteristics = $this->generateHistoricalCharacteristics($profile, $coursePattern, $baseStudent, $version);

        // CALCULAR SOLO LAS 4 VARIABLES OBJETIVO PRINCIPALES
        $targetVariables = $this->calculateTargetVariables($characteristics, $profile);

        $dates = $this->generateHistoricalDates($version);

        return [
            // IDENTIFICACIÓN
            'enrollment_id' => $baseStudent->enrollment_id * 100 + $version,
            'user_id' => $baseStudent->user_id,
            'group_id' => $baseStudent->group_id,
            'course_version_id' => $baseStudent->course_version_id,

            // COMPORTAMIENTO ACADÉMICO
            'avg_grade' => round($characteristics['avg_grade'], 2),
            'grade_std_dev' => round($characteristics['grade_std_dev'], 2),
            'total_exams_taken' => $characteristics['exam_count'],
            'grade_trend' => round($characteristics['grade_trend'], 3),
            'max_grade' => round($characteristics['max_grade'], 2),
            'min_grade' => round($characteristics['min_grade'], 2),
            'grade_range' => round($characteristics['grade_range'], 2),

            // ASISTENCIA Y PARTICIPACIÓN
            'attendance_rate' => round($characteristics['attendance_rate'], 2),
            'attendance_trend' => round($characteristics['attendance_trend'], 3),
            'total_sessions' => $characteristics['session_count'],
            'present_count' => $characteristics['present_count'],
            'recent_sessions_14d' => $characteristics['recent_sessions'],
            'exam_participation_rate' => round($characteristics['exam_participation_rate'], 2),

            // COMPORTAMIENTO FINANCIERO
            'payment_regularity' => round($characteristics['payment_regularity'], 2),
            'days_since_last_payment' => $characteristics['days_since_last_payment'],
            'avg_payment_delay' => round($characteristics['avg_payment_delay'], 1),
            'total_payments' => $characteristics['payment_count'],

            // CONTEXTO TEMPORAL
            'days_since_start' => $characteristics['days_since_start'],
            'days_until_end' => $characteristics['days_until_end'],
            'course_progress' => round($characteristics['course_progress'], 2),
            'sessions_progress' => round($characteristics['sessions_progress'], 2),

            // HISTORIAL PREVIO
            'previous_courses_completed' => $characteristics['previous_courses'],
            'historical_avg_grade' => round($characteristics['historical_avg_grade'], 2),
            'avg_satisfaction_score' => round($characteristics['avg_satisfaction_score'], 2),

            // 🎯 SOLO 4 VARIABLES OBJETIVO PRINCIPALES
            'dropped_out' => $targetVariables['dropped_out'],
            'approval_probability' => round($targetVariables['approval_probability'], 3),
            'final_grade_prediction' => round($targetVariables['final_grade_prediction'], 2),
            'academic_risk_level' => $targetVariables['academic_risk_level'],

            // METADATOS
            'student_name' => $baseStudent->student_name,
            'group_name' => $baseStudent->group_name . " - Histórico " . ($version + 1),
            'start_date' => $dates['start_date'],
            'end_date' => $dates['end_date'],
            'data_type' => 'historical_training',
            'historical_id' => $recordId + 1000,
            'student_profile' => $profileType
        ];
    }

    /**
     * CALCULA SOLO LAS 4 VARIABLES OBJETIVO PRINCIPALES
     */
    private function calculateTargetVariables(array $characteristics, array $profile): array
    {
        $avgGrade = $characteristics['avg_grade'];
        $attendanceRate = $characteristics['attendance_rate'];
        $paymentRegularity = $characteristics['payment_regularity'];
        $attendanceTrend = $characteristics['attendance_trend'];
        $gradeTrend = $characteristics['grade_trend'];

        // 1. PROBABILIDAD DE APROBACIÓN
        $approvalProbability = $this->calculateRealisticApprovalProbability(
            $avgGrade,
            $attendanceRate,
            $paymentRegularity,
            $attendanceTrend,
            $gradeTrend
        );

        // 2. ABANDONO
        $droppedOut = $this->determineDropoutFromRealData($approvalProbability, $characteristics);

        // 3. PREDICCIÓN DE NOTA FINAL
        $finalGradePrediction = $this->calculateFinalGradePrediction(
            $avgGrade,
            $gradeTrend,
            $characteristics['historical_avg_grade']
        );

        // 4. NIVEL DE RIESGO
        $academicRiskLevel = $this->calculateAcademicRiskLevel($approvalProbability);

        return [
            'dropped_out' => $droppedOut,
            'approval_probability' => round($approvalProbability, 3),
            'final_grade_prediction' => round($finalGradePrediction, 2),
            'academic_risk_level' => $academicRiskLevel
        ];
    }

    /**
     * CALCULA PREDICCIÓN DE NOTA FINAL (0-20)
     */
    private function calculateFinalGradePrediction(
        float $avgGrade,
        float $gradeTrend,
        float $historicalAvgGrade
    ): float {
        $currentWeight = 0.6;
        $trendWeight = 0.2;
        $historicalWeight = 0.2;

        $trendImpact = $gradeTrend * 5;

        $prediction = (
            $avgGrade * $currentWeight +
            ($avgGrade + $trendImpact) * $trendWeight +
            $historicalAvgGrade * $historicalWeight
        );

        return max(0, min(20, $prediction));
    }

    /**
     * CALCULA NIVEL DE RIESGO ACADÉMICO
     */
    private function calculateAcademicRiskLevel(float $approvalProbability): string
    {
        if ($approvalProbability >= 0.7) {
            return 'BAJO';
        } elseif ($approvalProbability >= 0.4) {
            return 'MEDIO';
        } else {
            return 'ALTO';
        }
    }

    /**
     * CALCULA PROBABILIDAD DE APROBACIÓN MÁS REALISTA
     */
    private function calculateRealisticApprovalProbability(
        float $avgGrade,
        float $attendanceRate,
        float $paymentRegularity,
        float $attendanceTrend,
        float $gradeTrend
    ): float {
        $gradeFactor = $avgGrade >= 11 ? 1.0 : ($avgGrade >= 8 ? 0.5 + (($avgGrade - 8) / 3) : ($avgGrade / 8));
        $attendanceFactor = $attendanceRate >= 80 ? 1.0 : ($attendanceRate >= 60 ? 0.5 + (($attendanceRate - 60) / 20) : ($attendanceRate / 60));
        $paymentFactor = $paymentRegularity;

        $probability = (
            $gradeFactor * 0.45 +
            $attendanceFactor * 0.35 +
            $paymentFactor * 0.20
        );

        return max(0.1, min(0.95, $probability));
    }

    /**
     * DETERMINA ABANDONO BASADO EN DATOS REALES
     */
    private function determineDropoutFromRealData(float $approvalProbability, array $characteristics): int
    {
        // 1. CASOS DE ALTO RIESGO - ABANDONO SEGURO
        $highRiskConditions =
            $characteristics['avg_grade'] < 8 ||
            $characteristics['attendance_rate'] < 50 ||
            $characteristics['payment_regularity'] < 0.4 ||
            $characteristics['days_since_last_payment'] > 60;

        if ($highRiskConditions && $approvalProbability < 0.4) {
            return rand(0, 100) < 85 ? 1 : 0;
        }

        // 2. CASOS DE BAJO RIESGO - NO ABANDONO SEGURO
        $lowRiskConditions =
            $characteristics['avg_grade'] > 14 &&
            $characteristics['attendance_rate'] > 80 &&
            $characteristics['payment_regularity'] > 0.8;

        if ($lowRiskConditions && $approvalProbability > 0.7) {
            return rand(0, 100) < 5 ? 1 : 0;
        }

        // 3. CASOS MEDIOS - BASADO EN PROBABILIDAD
        $dropoutChance = (1 - $approvalProbability) * 100;

        if ($characteristics['attendance_rate'] < 60) $dropoutChance += 20;
        if ($characteristics['avg_grade'] < 10) $dropoutChance += 15;
        if ($characteristics['payment_regularity'] < 0.5) $dropoutChance += 10;

        return rand(0, 100) < min(95, $dropoutChance) ? 1 : 0;
    }

    /**
     * Versión del dataset histórico SIN variables objetivo para predicción
     */
    public function generateHistoricalDatasetForPrediction(): array
    {
        $historicalData = $this->generateExtendedHistoricalDataset(200);

        // Remover SOLO las 4 variables objetivo principales
        return array_map(function ($record) {
            unset($record['dropped_out']);
            unset($record['approval_probability']);
            unset($record['final_grade_prediction']);
            unset($record['academic_risk_level']);
            unset($record['data_type']);
            unset($record['historical_id']);
            unset($record['student_profile']);
            return $record;
        }, $historicalData);
    }

    /**
     * Genera dataset actual para predicción
     */
    public function generateCurrentPredictionDataset(): array
    {
        $data = DB::table('enrollments as e')
            ->select([
                // IDENTIFICACIÓN
                'e.id as enrollment_id',
                'e.user_id',
                'e.group_id',
                'g.course_version_id',

                // COMPORTAMIENTO ACADÉMICO (con valores por defecto para NULL)
                DB::raw('COALESCE(ROUND(AVG(gr.grade), 2), 0) as avg_grade'),
                DB::raw('COALESCE(ROUND(STDDEV(gr.grade), 2), 0) as grade_std_dev'),
                DB::raw('COALESCE(COUNT(gr.id), 0) as total_exams_taken'),
                DB::raw('COALESCE(ROUND((MAX(gr.grade) - MIN(gr.grade)) / GREATEST(COUNT(gr.id), 1), 3), 0) as grade_trend'),
                DB::raw('COALESCE(ROUND(MAX(gr.grade), 2), 0) as max_grade'),
                DB::raw('COALESCE(ROUND(MIN(gr.grade), 2), 0) as min_grade'),
                DB::raw('COALESCE(ROUND(MAX(gr.grade) - MIN(gr.grade), 2), 0) as grade_range'),

                // ASISTENCIA Y PARTICIPACIÓN (con valores por defecto)
                DB::raw('COALESCE(ROUND((SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) / GREATEST(COUNT(a.id), 1)) * 100, 2), 0) as attendance_rate'),
                DB::raw('COALESCE(ROUND(
                    (COUNT(CASE WHEN a.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND a.status = "present" THEN a.id END) - 
                     COUNT(CASE WHEN a.created_at < DATE_SUB(NOW(), INTERVAL 14 DAY) AND a.created_at >= DATE_SUB(NOW(), INTERVAL 28 DAY) AND a.status = "present" THEN a.id END)
                    ) / GREATEST(COUNT(CASE WHEN a.created_at >= DATE_SUB(NOW(), INTERVAL 28 DAY) THEN a.id END), 1), 3
                ), 0) as attendance_trend'),
                DB::raw('COALESCE(COUNT(a.id), 0) as total_sessions'),
                DB::raw('COALESCE(SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END), 0) as present_count'),
                DB::raw('COALESCE(COUNT(CASE WHEN a.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) THEN a.id END), 0) as recent_sessions_14d'),
                DB::raw('COALESCE(ROUND(COUNT(gr.id) / GREATEST((SELECT COUNT(ex.id) FROM exams ex WHERE ex.group_id = g.id), 1), 2), 0) as exam_participation_rate'),

                // COMPORTAMIENTO FINANCIERO (con valores por defecto)
                DB::raw('COALESCE(ROUND(SUM(CASE WHEN ep.status = "approved" THEN 1 ELSE 0 END) / GREATEST(COUNT(ep.id), 1), 2), 0) as payment_regularity'),
                DB::raw('COALESCE(DATEDIFF(CURDATE(), MAX(ep.operation_date)), 90) as days_since_last_payment'),
                DB::raw('COALESCE(ROUND(AVG(CASE WHEN ep.status = "approved" THEN DATEDIFF(ep.operation_date, g.start_date) ELSE NULL END), 1), 0) as avg_payment_delay'),
                DB::raw('COALESCE(COUNT(ep.id), 0) as total_payments'),

                // CONTEXTO TEMPORAL (siempre disponibles)
                DB::raw('DATEDIFF(CURDATE(), g.start_date) as days_since_start'),
                DB::raw('DATEDIFF(g.end_date, CURDATE()) as days_until_end'),
                DB::raw('ROUND(DATEDIFF(CURDATE(), g.start_date) / GREATEST(DATEDIFF(g.end_date, g.start_date), 1), 2) as course_progress'),
                DB::raw('COALESCE(ROUND(COUNT(DISTINCT CASE WHEN cs.start_time <= NOW() THEN cs.id END) / GREATEST(COUNT(DISTINCT cs.id), 1), 2), 0) as sessions_progress'),

                // HISTORIAL PREVIO (con valores por defecto)
                DB::raw('COALESCE((SELECT COUNT(DISTINCT e2.id) FROM enrollments e2 WHERE e2.user_id = e.user_id AND e2.academic_status = "completed"), 0) as previous_courses_completed'),
                DB::raw('COALESCE((SELECT ROUND(AVG(er.final_grade), 2) FROM enrollment_results er JOIN enrollments e2 ON er.enrollment_id = e2.id WHERE e2.user_id = e.user_id), 10) as historical_avg_grade'),
                DB::raw('COALESCE((SELECT ROUND(AVG(rd.score), 2) FROM response_details rd JOIN survey_responses sr ON rd.survey_response_id = sr.id WHERE sr.user_id = e.user_id), 3) as avg_satisfaction_score'),

                // METADATOS
                'u.name as student_name',
                'g.name as group_name',
                'g.start_date',
                'g.end_date'
            ])
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('grades as gr', 'e.id', '=', 'gr.enrollment_id')
            ->leftJoin('attendances as a', 'e.id', '=', 'a.enrollment_id')
            ->leftJoin('enrollment_payments as ep', 'e.id', '=', 'ep.enrollment_id')
            ->leftJoin('class_sessions as cs', 'g.id', '=', 'cs.group_id')
            ->where('e.academic_status', 'active')
            ->where('g.end_date', '>=', now())
            ->groupBy('e.id', 'e.user_id', 'e.group_id', 'g.course_version_id', 'u.name', 'g.name', 'g.start_date', 'g.end_date')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        // 🎯 POST-PROCESAMIENTO: Asegurar tipos de datos consistentes
        return array_map([$this, 'ensureDataTypes'], $data);
    }

    /**
     * Asegura tipos de datos consistentes en el dataset de predicción
     */
    private function ensureDataTypes(array $record): array
    {
        // Convertir NULL a 0 y asegurar tipos numéricos
        $numericFields = [
            'avg_grade', 'grade_std_dev', 'total_exams_taken', 'grade_trend',
            'max_grade', 'min_grade', 'grade_range', 'attendance_rate', 
            'attendance_trend', 'total_sessions', 'present_count', 'recent_sessions_14d',
            'exam_participation_rate', 'payment_regularity', 'days_since_last_payment',
            'avg_payment_delay', 'total_payments', 'days_since_start', 'days_until_end',
            'course_progress', 'sessions_progress', 'previous_courses_completed',
            'historical_avg_grade', 'avg_satisfaction_score'
        ];

        foreach ($numericFields as $field) {
            if (!isset($record[$field]) || $record[$field] === null) {
                $record[$field] = 0;
            } else {
                $record[$field] = (float) $record[$field];
            }
        }

        return $record;
    }

    // ======================================================
    // MÉTODOS AUXILIARES (sin cambios)
    // ======================================================

    private function initializeStudentProfiles()
    {
        $this->studentProfiles = [
            'excelente' => [
                'grade_range' => [15, 20],
                'attendance_range' => [85, 100],
                'payment_regularity' => [0.9, 1.0],
                'participation_range' => [80, 100],
                'dropout_probability' => 0.02
            ],
            'bueno' => [
                'grade_range' => [11, 16],
                'attendance_range' => [75, 90],
                'payment_regularity' => [0.7, 0.9],
                'participation_range' => [60, 85],
                'dropout_probability' => 0.10
            ],
            'regular' => [
                'grade_range' => [8, 13],
                'attendance_range' => [60, 80],
                'payment_regularity' => [0.5, 0.8],
                'participation_range' => [40, 70],
                'dropout_probability' => 0.25
            ],
            'riesgo' => [
                'grade_range' => [0, 10],
                'attendance_range' => [30, 70],
                'payment_regularity' => [0.1, 0.6],
                'participation_range' => [10, 50],
                'dropout_probability' => 0.60
            ]
        ];
    }

    private function initializeCoursePatterns()
    {
        $this->coursePatterns = [
            'IA-DS' => [
                'session_count' => [12, 16],
                'duration_days' => [75, 90]
            ],
            'GP-TD' => [
                'session_count' => [10, 14],
                'duration_days' => [60, 80]
            ],
            'DW-CC' => [
                'session_count' => [11, 15],
                'duration_days' => [70, 85]
            ]
        ];
    }

    private function getBaseStudentData(): array
    {
        return DB::table('users as u')
            ->select([
                'u.id as user_id',
                'u.name as student_name',
                'e.id as enrollment_id',
                'e.group_id',
                'g.name as group_name',
                'g.course_version_id',
                'cv.name as course_name',
                DB::raw('COALESCE(AVG(gr.grade), 10) as base_grade')
            ])
            ->join('enrollments as e', 'u.id', '=', 'e.user_id')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->leftJoin('grades as gr', 'e.id', '=', 'gr.enrollment_id')
            ->where('u.id', '>', 4)
            ->groupBy('u.id', 'u.name', 'e.id', 'e.group_id', 'g.name', 'g.course_version_id', 'cv.name')
            ->get()
            ->toArray();
    }

    private function determineStudentProfile(object $student): string
    {
        $baseGrade = $student->base_grade ?? 10;
        if ($baseGrade >= 15) return 'excelente';
        if ($baseGrade >= 11) return 'bueno';
        if ($baseGrade >= 8) return 'regular';
        return 'riesgo';
    }

    private function getCoursePattern(string $courseName): array
    {
        foreach ($this->coursePatterns as $key => $pattern) {
            if (str_contains($courseName, $key)) return $pattern;
        }
        return $this->coursePatterns['GP-TD'];
    }

    private function generateHistoricalCharacteristics(array $profile, array $coursePattern, object $student, int $version): array
    {
        $gradeRange = $profile['grade_range'];
        $attendanceRange = $profile['attendance_range'];
        $paymentRegularityRange = $profile['payment_regularity'];
        $participationRange = $profile['participation_range'];

        $avgGrade = rand($gradeRange[0] * 10, $gradeRange[1] * 10) / 10;
        $attendanceRate = rand($attendanceRange[0], $attendanceRange[1]);
        $paymentRegularity = rand($paymentRegularityRange[0] * 100, $paymentRegularityRange[1] * 100) / 100;
        $participationRate = rand($participationRange[0], $participationRange[1]);

        $sessionCount = rand($coursePattern['session_count'][0], $coursePattern['session_count'][1]);
        $courseDuration = rand($coursePattern['duration_days'][0], $coursePattern['duration_days'][1]);
        $daysSinceStart = rand(30, $courseDuration);

        $willDropout = $this->willStudentDropout($profile, $avgGrade, $attendanceRate, $paymentRegularity);

        if ($willDropout) {
            $avgGrade = max(0, $avgGrade - rand(3, 8));
            $attendanceRate = max(0, $attendanceRate - rand(20, 50));
            $paymentRegularity = max(0, $paymentRegularity - rand(0.3, 0.7));
            $participationRate = max(0, $participationRate - rand(20, 40));

            $avgGrade = min(13, $avgGrade);
            $attendanceRate = min(70, $attendanceRate);
        }

        $courseProgress = $courseDuration > 0 ? ($daysSinceStart / $courseDuration) : 0;
        $sessionsProgress = $sessionCount > 0 ? (rand(1, $sessionCount) / $sessionCount) : 0;

        return [
            'avg_grade' => $avgGrade,
            'grade_std_dev' => $willDropout ? rand(15, 35) / 10 : rand(5, 25) / 10,
            'exam_count' => $willDropout ? rand(1, 4) : rand(3, 8),
            'grade_trend' => $willDropout ? (rand(-20, -5) / 100) : (rand(-10, 10) / 100),
            'max_grade' => min(20, $avgGrade + ($willDropout ? rand(0, 2) : rand(1, 3))),
            'min_grade' => max(0, $avgGrade - ($willDropout ? rand(2, 5) : rand(1, 3))),
            'grade_range' => rand(2, 8) + (rand(0, 9) / 10),
            
            'attendance_rate' => $attendanceRate,
            'attendance_trend' => $willDropout ? (rand(-25, -10) / 100) : (rand(-15, 5) / 100),
            'session_count' => $sessionCount,
            'present_count' => round(($attendanceRate / 100) * $sessionCount),
            'recent_sessions' => $willDropout ? rand(0, 3) : rand(0, 8),
            'exam_participation_rate' => $participationRate / 100,
            
            'payment_regularity' => $paymentRegularity,
            'days_since_last_payment' => $willDropout ? rand(30, 90) : rand(0, 45),
            'avg_payment_delay' => $willDropout ? rand(15, 30) : rand(0, 15),
            'payment_count' => $willDropout ? rand(0, 2) : rand(1, 4),
            
            'days_since_start' => $daysSinceStart,
            'days_until_end' => max(0, $courseDuration - $daysSinceStart),
            'course_progress' => min(1.0, $courseProgress),
            'sessions_progress' => $sessionsProgress,
            
            'previous_courses' => rand(0, 3),
            'historical_avg_grade' => max(0, min(20, $avgGrade + (rand(-5, 5) / 10))),
            'avg_satisfaction_score' => $willDropout ? rand(15, 35) / 10 : rand(25, 50) / 10
        ];
    }

    private function willStudentDropout(array $profile, float $avgGrade, float $attendanceRate, float $paymentRegularity): bool
    {
        $baseProbability = $profile['dropout_probability'];
        $gradeFactor = $avgGrade < 10 ? 0.3 : ($avgGrade < 13 ? 0.1 : 0);
        $attendanceFactor = $attendanceRate < 60 ? 0.3 : ($attendanceRate < 75 ? 0.1 : 0);
        $paymentFactor = $paymentRegularity < 0.4 ? 0.3 : ($paymentRegularity < 0.7 ? 0.1 : 0);

        $totalProbability = $baseProbability + $gradeFactor + $attendanceFactor + $paymentFactor;
        $randomFactor = rand(0, 20) / 100;

        return (rand(0, 100) < (($totalProbability + $randomFactor) * 100));
    }

    private function generateHistoricalDates(int $version): array
    {
        $monthsAgo = 6 + ($version * 3);
        $duration = rand(2, 4);
        $startDate = Carbon::now()->subMonths($monthsAgo);
        $endDate = (clone $startDate)->addMonths($duration);

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ];
    }

    private function generateSyntheticDataset(int $numRecords): array
    {
        $dataset = [];
        $profileTypes = array_keys($this->studentProfiles);

        for ($i = 0; $i < $numRecords; $i++) {
            $profileType = $profileTypes[array_rand($profileTypes)];

            $dataset[] = $this->createHistoricalRecord(
                (object)[
                    'enrollment_id' => $i + 1,
                    'user_id' => $i + 100,
                    'group_id' => ($i % 3) + 1,
                    'group_name' => 'Curso ' . array_rand($this->coursePatterns),
                    'student_name' => 'Estudiante ' . ($i + 1),
                    'course_version_id' => ($i % 3) + 1,
                    'course_name' => array_rand($this->coursePatterns),
                    'base_grade' => 10
                ],
                0,
                $i
            );
        }

        return $dataset;
    }

    // ======================================================
    // MÉTODOS DE EXPORTACIÓN (optimizados)
    // ======================================================

    public function exportExtendedHistoricalDatasetToCsv(int $numRecords = 1000): string
    {
        $data = $this->generateExtendedHistoricalDataset($numRecords);
        return $this->exportToCsv($data, "historical_training_{$numRecords}");
    }

    public function exportCurrentPredictionDatasetToCsv(): string
    {
        $data = $this->generateCurrentPredictionDataset();
        return $this->exportToCsv($data, 'current_prediction');
    }

    public function exportHistoricalForPredictionToCsv(int $numRecords = 200): string
    {
        $data = $this->generateHistoricalDatasetForPrediction();
        return $this->exportToCsv($data, "historical_prediction_{$numRecords}");
    }

    private function exportToCsv(array $data, string $type): string
    {
        if (empty($data)) {
            throw new \Exception("No hay datos para exportar en {$type}");
        }

        $filename = "dropout_{$type}_" . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $handle = fopen($filepath, 'w');
        $headers = array_keys($data[0]);
        fputcsv($handle, $headers);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $filepath;
    }

    /**
     * Método para análisis de distribución
     */
    public function analyzeDropoutDistribution(int $sampleSize = 500): array
    {
        $data = $this->generateExtendedHistoricalDataset($sampleSize);

        $analysis = [
            'total_records' => count($data),
            'dropped_out_count' => 0,
            'dropped_out_percentage' => 0,
            'by_profile' => [],
            'by_risk_level' => []
        ];

        foreach ($data as $record) {
            if ($record['dropped_out'] == 1) {
                $analysis['dropped_out_count']++;
                $profile = $record['student_profile'];
                $analysis['by_profile'][$profile] = ($analysis['by_profile'][$profile] ?? 0) + 1;
                $riskLevel = $record['academic_risk_level'];
                $analysis['by_risk_level'][$riskLevel] = ($analysis['by_risk_level'][$riskLevel] ?? 0) + 1;
            }
        }

        $analysis['dropped_out_percentage'] = round(
            ($analysis['dropped_out_count'] / $analysis['total_records']) * 100, 2
        );

        return $analysis;
    }
}