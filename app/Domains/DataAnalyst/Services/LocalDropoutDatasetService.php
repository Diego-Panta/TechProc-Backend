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
     * Genera dataset histórico con las MISMAS columnas que el dataset actual
     */
    public function generateExtendedHistoricalDataset(int $numRecords = 300): array
    {
        $historicalData = [];
        $baseStudents = $this->getBaseStudentData();
        
        if (empty($baseStudents)) {
            return $this->generateSyntheticDataset($numRecords);
        }

        $recordsPerStudent = ceil($numRecords / count($baseStudents));
        
        foreach ($baseStudents as $student) {
            for ($i = 0; $i < $recordsPerStudent && count($historicalData) < $numRecords; $i++) {
                $historicalData[] = $this->createHistoricalRecordWithConsistentColumns($student, $i, count($historicalData));
            }
        }

        return $historicalData;
    }

    /**
     * Crea registro histórico con columnas IDÉNTICAS al dataset actual
     */
    private function createHistoricalRecordWithConsistentColumns(object $baseStudent, int $version, int $recordId): array
    {
        $profileType = $this->determineStudentProfile($baseStudent);
        $profile = $this->studentProfiles[$profileType];
        $coursePattern = $this->getCoursePattern($baseStudent->course_name);
        
        $characteristics = $this->generateRealisticCharacteristics($profile, $coursePattern, $baseStudent, $version);
        $droppedOut = $this->determineRealisticDropout($profile, $characteristics, $version);
        $dates = $this->generateHistoricalDates($version);

        // USAR EXACTAMENTE LAS MISMAS COLUMNAS QUE EL DATASET ACTUAL
        return [
            // Columnas básicas (iguales al dataset actual)
            'enrollment_id' => $baseStudent->enrollment_id * 100 + $version,
            'user_id' => $baseStudent->user_id,
            'group_id' => $baseStudent->group_id,
            'group_name' => $baseStudent->group_name . " - Histórico " . ($version + 1),
            'student_name' => $baseStudent->student_name,
            'academic_status' => $droppedOut ? 'dropped' : 'completed', // Para histórico
            'payment_status' => $characteristics['pending_payments'] > 0 ? 'pending' : 'paid',
            
            // Características académicas (MISMOS TIPOS que dataset actual)
            'avg_grade' => round($characteristics['avg_grade'], 2), // FLOAT
            'total_exams_taken' => $characteristics['exam_count'], // INT
            'max_grade' => round($characteristics['max_grade'], 2), // FLOAT
            'min_grade' => round($characteristics['min_grade'], 2), // FLOAT
            'grade_range' => round($characteristics['grade_range'], 2), // FLOAT (corregido)
            
            // Características de asistencia (MISMOS TIPOS)
            'total_sessions' => $characteristics['session_count'], // INT
            'present_count' => $characteristics['present_count'], // INT
            'attendance_rate' => round($characteristics['attendance_rate'], 2), // FLOAT
            'recent_sessions_14d' => $characteristics['recent_sessions'], // INT (usando 14d como el actual)
            
            // Características de pagos (MISMOS TIPOS)
            'total_payments' => $characteristics['payment_count'], // INT
            'pending_payments' => $characteristics['pending_payments'], // INT
            'rejected_payments' => $characteristics['rejected_payments'], // INT
            
            // Características de soporte (MISMOS TIPOS)
            'total_tickets' => $characteristics['ticket_count'], // INT
            'open_tickets' => $characteristics['open_tickets'], // INT
            
            // Características temporales (MISMOS TIPOS)
            'days_since_start' => $characteristics['days_since_start'], // INT
            'days_until_end' => $characteristics['days_until_end'], // INT
            
            // Indicadores de riesgo (MISMOS TIPOS - INT como flags)
            'low_performance_flag' => $characteristics['avg_grade'] < 11 ? 1 : 0, // INT
            'low_attendance_flag' => $characteristics['attendance_rate'] < 70 ? 1 : 0, // INT
            'pending_payments_flag' => $characteristics['pending_payments'] > 0 ? 1 : 0, // INT
            'many_open_tickets_flag' => $characteristics['open_tickets'] > 2 ? 1 : 0, // INT
            
            // Metadatos adicionales para histórico
            'start_date' => $dates['start_date'],
            'end_date' => $dates['end_date'],
            'course_version_id' => $baseStudent->course_version_id,
            
            // Variable objetivo (para entrenamiento)
            'dropped_out' => $droppedOut, // INT - SOLO para dataset histórico
            
            // Columnas adicionales para tracking
            'data_type' => 'historical_training',
            'historical_id' => $recordId + 1000,
            'student_profile' => $profileType
        ];
    }

    /**
     * Genera dataset actual para predicción (MANTENER ORIGINAL)
     */
    public function generateCurrentPredictionDataset(): array
    {
        return DB::table('enrollments as e')
            ->select([
                'e.id as enrollment_id',
                'e.user_id',
                'e.group_id',
                'g.name as group_name',
                'u.name as student_name',
                'e.academic_status',
                'e.payment_status',
                
                // Características académicas (TIPOS ORIGINALES)
                DB::raw('ROUND(AVG(gr.grade), 2) as avg_grade'), // FLOAT
                DB::raw('COUNT(gr.id) as total_exams_taken'), // INT
                DB::raw('ROUND(MAX(gr.grade), 2) as max_grade'), // FLOAT
                DB::raw('ROUND(MIN(gr.grade), 2) as min_grade'), // FLOAT
                DB::raw('ROUND(MAX(gr.grade) - MIN(gr.grade), 2) as grade_range'), // FLOAT
                
                // Características de asistencia (TIPOS ORIGINALES)
                DB::raw('COUNT(a.id) as total_sessions'), // INT
                DB::raw('SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) as present_count'), // INT
                DB::raw('ROUND((SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_rate'), // FLOAT
                DB::raw('COUNT(CASE WHEN a.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) THEN a.id END) as recent_sessions_14d'), // INT
                
                // Características de pagos (TIPOS ORIGINALES)
                DB::raw('COUNT(ep.id) as total_payments'), // INT
                DB::raw('SUM(CASE WHEN ep.status = "pending" THEN 1 ELSE 0 END) as pending_payments'), // INT
                DB::raw('SUM(CASE WHEN ep.status = "rejected" THEN 1 ELSE 0 END) as rejected_payments'), // INT
                
                // Características de soporte (TIPOS ORIGINALES)
                DB::raw('COUNT(DISTINCT t.id) as total_tickets'), // INT
                DB::raw('COUNT(DISTINCT CASE WHEN t.status = "open" THEN t.id END) as open_tickets'), // INT
                
                // Características temporales (TIPOS ORIGINALES)
                DB::raw('DATEDIFF(CURDATE(), g.start_date) as days_since_start'), // INT
                DB::raw('DATEDIFF(g.end_date, CURDATE()) as days_until_end'), // INT
                
                // Indicadores de riesgo (TIPOS ORIGINALES - INT)
                DB::raw('CASE WHEN AVG(gr.grade) < 11 THEN 1 ELSE 0 END as low_performance_flag'), // INT
                DB::raw('CASE WHEN (SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) / COUNT(a.id)) * 100 < 70 THEN 1 ELSE 0 END as low_attendance_flag'), // INT
                DB::raw('CASE WHEN SUM(CASE WHEN ep.status = "pending" THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END as pending_payments_flag'), // INT
                DB::raw('CASE WHEN COUNT(DISTINCT CASE WHEN t.status = "open" THEN t.id END) > 2 THEN 1 ELSE 0 END as many_open_tickets_flag'), // INT
                
                // Metadatos
                'g.start_date',
                'g.end_date',
                'g.course_version_id',
                'data_type' => DB::raw("'current_prediction'")
            ])
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('grades as gr', 'e.id', '=', 'gr.enrollment_id')
            ->leftJoin('attendances as a', 'e.id', '=', 'a.enrollment_id')
            ->leftJoin('enrollment_payments as ep', 'e.id', '=', 'ep.enrollment_id')
            ->leftJoin('tickets as t', 'e.user_id', '=', 't.user_id')
            ->where('e.academic_status', 'active')
            ->where('g.end_date', '>=', now())
            ->groupBy('e.id', 'e.user_id', 'e.group_id', 'g.name', 'u.name', 'e.academic_status', 
                     'e.payment_status', 'g.start_date', 'g.end_date', 'g.course_version_id')
            ->having('total_sessions', '>', 0)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    /**
     * Versión del dataset histórico SIN la columna dropped_out para predicción
     */
    public function generateHistoricalDatasetForPrediction(): array
    {
        $historicalData = $this->generateExtendedHistoricalDataset(100);
        
        // Remover la columna dropped_out y otras columnas de entrenamiento
        return array_map(function($record) {
            unset($record['dropped_out']);
            unset($record['data_type']);
            unset($record['historical_id']);
            unset($record['student_profile']);
            return $record;
        }, $historicalData);
    }

    // ... (mantener los mismos métodos auxiliares sin cambios)

    private function initializeStudentProfiles()
    {
        $this->studentProfiles = [
            'excelente' => [
                'grade_range' => [15, 20],
                'attendance_range' => [85, 100],
                'payment_issues' => 0.05,
                'tickets_range' => [0, 2],
                'dropout_probability' => 0.05
            ],
            'bueno' => [
                'grade_range' => [11, 16],
                'attendance_range' => [75, 90],
                'payment_issues' => 0.15,
                'tickets_range' => [0, 3],
                'dropout_probability' => 0.15
            ],
            'regular' => [
                'grade_range' => [8, 13],
                'attendance_range' => [60, 80],
                'payment_issues' => 0.30,
                'tickets_range' => [1, 5],
                'dropout_probability' => 0.35
            ],
            'riesgo' => [
                'grade_range' => [0, 10],
                'attendance_range' => [30, 70],
                'payment_issues' => 0.60,
                'tickets_range' => [3, 8],
                'dropout_probability' => 0.70
            ]
        ];
    }

    private function initializeCoursePatterns()
    {
        $this->coursePatterns = [
            'IA-DS' => ['session_count' => [12, 16]],
            'GP-TD' => ['session_count' => [10, 14]],
            'DW-CC' => ['session_count' => [11, 15]]
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

    private function generateRealisticCharacteristics(array $profile, array $coursePattern, object $student, int $version): array
    {
        $gradeRange = $profile['grade_range'];
        $attendanceRange = $profile['attendance_range'];
        
        $avgGrade = rand($gradeRange[0] * 10, $gradeRange[1] * 10) / 10;
        $attendanceRate = rand($attendanceRange[0], $attendanceRange[1]);
        $sessionCount = rand($coursePattern['session_count'][0], $coursePattern['session_count'][1]);
        
        return [
            'avg_grade' => $avgGrade,
            'exam_count' => rand(3, 8),
            'max_grade' => min(20, $avgGrade + rand(1, 3)),
            'min_grade' => max(0, $avgGrade - rand(1, 3)),
            'grade_range' => rand(2, 8) + (rand(0, 9) / 10), // FLOAT con decimal
            'session_count' => $sessionCount,
            'present_count' => round(($attendanceRate / 100) * $sessionCount),
            'attendance_rate' => $attendanceRate,
            'recent_sessions' => rand(0, 8),
            'payment_count' => rand(1, 4),
            'approved_payments' => rand(1, 3),
            'pending_payments' => (rand(0, 100) < ($profile['payment_issues'] * 100)) ? rand(1, 2) : 0,
            'rejected_payments' => rand(0, 1),
            'ticket_count' => rand($profile['tickets_range'][0], $profile['tickets_range'][1]),
            'open_tickets' => rand(0, 2),
            'days_since_start' => rand(30, 180),
            'days_until_end' => rand(0, 60)
        ];
    }

    private function determineRealisticDropout(array $profile, array $characteristics, int $version): int
    {
        $dropoutProbability = $profile['dropout_probability'];
        if ($characteristics['avg_grade'] < 8) $dropoutProbability += 0.3;
        if ($characteristics['attendance_rate'] < 50) $dropoutProbability += 0.2;
        if ($characteristics['pending_payments'] > 1) $dropoutProbability += 0.15;
        return (rand(0, 100) < ($dropoutProbability * 100)) ? 1 : 0;
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
            $profile = $this->studentProfiles[$profileType];
            $coursePattern = $this->coursePatterns[array_rand($this->coursePatterns)];
            
            $dataset[] = $this->createHistoricalRecordWithConsistentColumns(
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

    // Métodos de exportación
    public function exportExtendedHistoricalDatasetToCsv(int $numRecords = 300): string
    {
        $data = $this->generateExtendedHistoricalDataset($numRecords);
        return $this->exportToCsv($data, "historical_training_{$numRecords}");
    }

    public function exportCurrentPredictionDatasetToCsv(): string
    {
        $data = $this->generateCurrentPredictionDataset();
        return $this->exportToCsv($data, 'current_prediction');
    }

    public function exportHistoricalForPredictionToCsv(int $numRecords = 100): string
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
}