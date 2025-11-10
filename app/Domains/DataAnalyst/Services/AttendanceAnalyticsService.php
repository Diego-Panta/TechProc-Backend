<?php
// app/Domains/DataAnalyst/Services/AttendanceAnalyticsService.php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Models\DataAnalytic;
use App\Domains\DataAnalyst\Repositories\AttendanceDataRepository;
use IncadevUns\CoreDomain\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use IncadevUns\CoreDomain\Enums\EnrollmentAcademicStatus;
use IncadevUns\CoreDomain\Enums\GroupStatus;

class AttendanceAnalyticsService
{
    public function __construct(
        private AttendanceDataRepository $attendanceRepository
    ) {}

    /**
     * Calcula y guarda el análisis de asistencia usando la tabla polimórfica
     */
    public function calculateAndSaveAttendanceAnalytics(int $enrollmentId, string $period = '30d'): DataAnalytic
    {
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('academic_status', EnrollmentAcademicStatus::Active->value)
            ->firstOrFail();

        $analysis = $this->calculateAttendanceAnalysis($enrollmentId, $period);

        $formattedData = $this->formatAttendanceData($analysis);

        return DataAnalytic::updateOrCreate(
            [
                'analyzable_type' => get_class($enrollment),
                'analyzable_id' => $enrollment->id,
                'analysis_type' => 'attendance',
                'period' => $period,
            ],
            array_merge($formattedData, [
                'calculated_at' => now(),
            ])
        );
    }

    /**
     * Obtiene o calcula el análisis de asistencia
     */
    public function getAttendanceAnalysis(int $enrollmentId, string $period = '30d', bool $refresh = false): ?DataAnalytic
    {
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('academic_status', EnrollmentAcademicStatus::Active->value)
            ->first();

        if (!$enrollment) {
            return null;
        }

        if (!$refresh) {
            $existing = DataAnalytic::where([
                'analyzable_type' => get_class($enrollment),
                'analyzable_id' => $enrollment->id,
                'analysis_type' => 'attendance',
                'period' => $period,
            ])->first();

            if ($existing) {
                return $existing;
            }
        }

        return $this->calculateAndSaveAttendanceAnalytics($enrollmentId, $period);
    }

    /**
     * Calcula el análisis completo de asistencia
     */
    private function calculateAttendanceAnalysis(int $enrollmentId, string $period = '30d'): array
    {
        $attendanceData = $this->attendanceRepository->getStudentAttendanceData($enrollmentId, $period);

        if (empty($attendanceData)) {
            throw new \Exception("No se encontraron datos de asistencia para la matrícula: {$enrollmentId}");
        }

        return array_merge($attendanceData, [
            'attendance_rate' => $this->calculateAttendanceRate($attendanceData),
            'attendance_trend' => $this->calculateAttendanceTrend($attendanceData),
            'risk_assessment' => $this->assessAttendanceRisk($attendanceData),
            'patterns' => $this->analyzeAttendancePatterns($attendanceData),
            'comparison' => $this->generateBenchmarkComparison($attendanceData),
            'recommendations' => $this->generateAttendanceRecommendations($attendanceData),
        ]);
    }

    /**
     * Formatea datos para almacenamiento en DataAnalytic
     */
    private function formatAttendanceData(array $analysis): array
    {
        return [
            'score' => $analysis['attendance_rate'] ?? null,
            'rate' => $analysis['attendance_rate'] ?? null,
            'total_events' => $analysis['total_sessions'] ?? null,
            'completed_events' => $analysis['attended_sessions'] ?? null,
            'risk_level' => $analysis['risk_assessment']['risk_level'] ?? null,
            'metrics' => [
                'absent_sessions' => $analysis['absent_sessions'] ?? null,
                'late_sessions' => $analysis['late_sessions'] ?? null,
                'consecutive_absences' => $analysis['max_consecutive_absences'] ?? null,
                'last_attendance_date' => $analysis['last_attendance_date'] ?? null,
                'group_name' => $analysis['group_name'] ?? null,
            ],
            'trends' => [
                'attendance_trend' => $analysis['attendance_trend'] ?? null,
                'consistency_score' => $analysis['patterns']['consistency']['consistency_score'] ?? null,
            ],
            'patterns' => $analysis['patterns'] ?? null,
            'comparisons' => $analysis['comparison'] ?? null,
            'triggers' => $analysis['risk_assessment']['triggers'] ?? null,
            'recommendations' => $analysis['recommendations'] ?? null,
        ];
    }

    /**
     * Calcula la tasa de asistencia
     */
    private function calculateAttendanceRate(array $data): float
    {
        $totalSessions = $data['total_sessions'] ?? 0;
        $attendedSessions = $data['attended_sessions'] ?? 0;

        if ($totalSessions === 0) {
            return 0.0;
        }

        return round(($attendedSessions / $totalSessions) * 100, 2);
    }

    /**
     * Calcula la tendencia de asistencia
     */
    private function calculateAttendanceTrend(array $data): float
    {
        $timeline = $data['attendance_timeline'] ?? [];

        if (count($timeline) < 2) {
            return 0.0;
        }

        // Dividir timeline en dos mitades para comparar
        $half = ceil(count($timeline) / 2);
        $firstHalf = array_slice($timeline, 0, $half);
        $secondHalf = array_slice($timeline, $half);

        $firstHalfRate = $this->calculateAverageRate($firstHalf);
        $secondHalfRate = $this->calculateAverageRate($secondHalf);

        return round($secondHalfRate - $firstHalfRate, 2);
    }

    private function calculateAverageRate(array $period): float
    {
        $total = 0;
        $count = 0;

        foreach ($period as $day) {
            if ($day->total_sessions > 0) {
                $total += ($day->attended / $day->total_sessions) * 100;
                $count++;
            }
        }

        return $count > 0 ? $total / $count : 0;
    }

    /**
     * Evalúa el riesgo basado en asistencia
     */
    private function assessAttendanceRisk(array $data): array
    {
        $attendanceRate = $this->calculateAttendanceRate($data);
        $consecutiveAbsences = $data['max_consecutive_absences'] ?? 0;
        $trend = $this->calculateAttendanceTrend($data);
        $totalSessions = $data['total_sessions'] ?? 0;

        // 🔥 NUEVA LÓGICA: Si no hay sesiones en el período, riesgo = "none"
        if ($totalSessions === 0) {
            return [
                'risk_score' => 0,
                'risk_level' => 'none',
                'triggers' => ['sin_sesiones_periodo'],
            ];
        }

        $riskScore = 0;

        // Tasa de asistencia (50% peso)
        if ($attendanceRate < 60) $riskScore += 50;
        elseif ($attendanceRate < 70) $riskScore += 35;
        elseif ($attendanceRate < 80) $riskScore += 20;
        elseif ($attendanceRate < 90) $riskScore += 10;

        // Ausencias consecutivas (30% peso)
        if ($consecutiveAbsences >= 5) $riskScore += 30;
        elseif ($consecutiveAbsences >= 3) $riskScore += 20;
        elseif ($consecutiveAbsences >= 2) $riskScore += 10;

        // Tendencia (20% peso)
        if ($trend < -10) $riskScore += 20;
        elseif ($trend < -5) $riskScore += 15;
        elseif ($trend < 0) $riskScore += 5;

        $riskLevel = $this->getAttendanceRiskLevel($riskScore);

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'triggers' => $this->identifyRiskTriggers($attendanceRate, $consecutiveAbsences, $trend, $totalSessions),
        ];
    }

    private function getAttendanceRiskLevel(int $score): string
    {
        return match (true) {
            $score >= 70 => 'critical',
            $score >= 50 => 'high',
            $score >= 30 => 'medium',
            $score >= 15 => 'low',
            default => 'none'
        };
    }

    private function identifyRiskTriggers(float $rate, int $consecutive, float $trend, int $totalSessions): array
    {
        $triggers = [];

        // Si no hay sesiones, no hay triggers de riesgo
        if ($totalSessions === 0) {
            return ['sin_sesiones_periodo'];
        }

        if ($rate < 70) $triggers[] = 'baja_asistencia_general';
        if ($rate < 60) $triggers[] = 'asistencia_critica';
        if ($consecutive >= 3) $triggers[] = 'ausencias_consecutivas';
        if ($trend < -5) $triggers[] = 'tendencia_decreciente';

        return $triggers;
    }

    /**
     * Analiza patrones de asistencia
     */
    private function analyzeAttendancePatterns(array $data): array
    {
        $timeline = $data['attendance_timeline'] ?? [];
        $byModule = $data['attendance_by_module'] ?? [];
        $weeklyPattern = $data['weekly_pattern'] ?? [];

        $patterns = [
            'weekly_variation' => $weeklyPattern, // Usar el nuevo método
            'module_performance' => $this->analyzeModulePattern($byModule),
            'consistency' => $this->analyzeConsistency($timeline),
        ];

        return $patterns;
    }

    private function analyzeWeeklyPattern(array $timeline): array
    {
        $weekly = [];
        foreach ($timeline as $day) {
            $date = Carbon::parse($day->date);
            $dayOfWeek = $date->dayOfWeek;

            if (!isset($weekly[$dayOfWeek])) {
                $weekly[$dayOfWeek] = ['total' => 0, 'attended' => 0];
            }

            $weekly[$dayOfWeek]['total'] += $day->total_sessions;
            $weekly[$dayOfWeek]['attended'] += $day->attended;
        }

        $result = [];
        foreach ($weekly as $day => $stats) {
            $result[$day] = [
                'day_name' => Carbon::create()->startOfWeek()->addDays($day)->dayName,
                'attendance_rate' => $stats['total'] > 0 ?
                    round(($stats['attended'] / $stats['total']) * 100, 2) : 0
            ];
        }

        return $result;
    }

    private function analyzeModulePattern(array $byModule): array
    {
        $modules = [];
        foreach ($byModule as $module) {
            $modules[] = [
                'module_name' => $module->module_name,
                'attendance_rate' => (float) $module->attendance_rate,
                'total_sessions' => $module->total_sessions,
            ];
        }
        return $modules;
    }

    private function analyzeConsistency(array $timeline): array
    {
        if (empty($timeline)) {
            return ['consistency_score' => 0, 'variation' => 0];
        }

        $rates = [];
        foreach ($timeline as $day) {
            if ($day->total_sessions > 0) {
                $rates[] = ($day->attended / $day->total_sessions) * 100;
            }
        }

        if (empty($rates)) {
            return ['consistency_score' => 0, 'variation' => 0];
        }

        $average = array_sum($rates) / count($rates);
        $variance = 0;
        foreach ($rates as $rate) {
            $variance += pow($rate - $average, 2);
        }
        $variance = $variance / count($rates);
        $stdDev = sqrt($variance);

        return [
            'consistency_score' => max(0, 100 - $stdDev),
            'variation' => $stdDev,
            'stable' => $stdDev < 15
        ];
    }

    /**
     * Genera comparativas con benchmarks
     */
    private function generateBenchmarkComparison(array $data): array
    {
        $studentRate = $this->calculateAttendanceRate($data);
        $groupAvg = $data['comparison_with_group']['group_average'] ?? 0;

        return [
            'group_average' => $groupAvg,
            'comparison_to_group' => round($studentRate - $groupAvg, 2),
            'institutional_benchmark' => 80.0,
            'gap_to_benchmark' => round($studentRate - 80.0, 2),
            'percentile' => $this->calculatePercentile($studentRate, $groupAvg),
        ];
    }

    private function calculatePercentile(float $studentRate, float $groupAvg): int
    {
        if ($groupAvg === 0) return 50;

        $deviation = $studentRate - $groupAvg;
        if ($deviation >= 10) return 90;
        if ($deviation >= 5) return 75;
        if ($deviation >= 0) return 60;
        if ($deviation >= -5) return 40;
        if ($deviation >= -10) return 25;
        return 10;
    }

    /**
     * Genera recomendaciones
     */
    private function generateAttendanceRecommendations(array $data): array
    {
        $riskAssessment = $this->assessAttendanceRisk($data);
        $patterns = $this->analyzeAttendancePatterns($data);
        $recommendations = [];

        if ($riskAssessment['risk_level'] === 'critical') {
            $recommendations[] = 'Contacto inmediato con el estudiante';
            $recommendations[] = 'Revisión de situación personal/académica';
        }

        if (in_array('baja_asistencia_general', $riskAssessment['triggers'])) {
            $recommendations[] = 'Plan de regularización de asistencia';
        }

        if (in_array('ausencias_consecutivas', $riskAssessment['triggers'])) {
            $recommendations[] = 'Seguimiento de ausencias consecutivas';
        }

        if (in_array('tendencia_decreciente', $riskAssessment['triggers'])) {
            $recommendations[] = 'Análisis de causas de disminución';
        }

        // Recomendaciones basadas en patrones
        $weeklyPattern = $patterns['weekly_variation'] ?? [];
        foreach ($weeklyPattern as $day) {
            if ($day['attendance_rate'] < 60) {
                $recommendations[] = "Refuerzo en asistencia los {$day['day_name']}";
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Asistencia satisfactoria - mantener seguimiento regular';
        }

        return array_slice($recommendations, 0, 5);
    }

    /**
     * Procesa análisis para todos los estudiantes de un grupo
     */
    public function calculateAttendanceForGroup(int $groupId, string $period = '30d'): array
    {
        $enrollments = DB::table('enrollments')
            ->where('group_id', $groupId)
            ->where('academic_status', 'active')
            ->pluck('id');

        $results = [
            'total_processed' => 0,
            'critical_risk' => 0,
            'high_risk' => 0,
            'medium_risk' => 0,
            'low_risk' => 0,
            'no_risk' => 0
        ];

        foreach ($enrollments as $enrollmentId) {
            try {
                $analytic = $this->calculateAndSaveAttendanceAnalytics($enrollmentId, $period);
                $results['total_processed']++;

                switch ($analytic->risk_level) {
                    case 'critical':
                        $results['critical_risk']++;
                        break;
                    case 'high':
                        $results['high_risk']++;
                        break;
                    case 'medium':
                        $results['medium_risk']++;
                        break;
                    case 'low':
                        $results['low_risk']++;
                        break;
                    case 'none':
                        $results['no_risk']++;
                        break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $results;
    }

    /**
     * Obtiene estadísticas de asistencia para un grupo
     */
    public function getGroupAttendanceStats(int $groupId, string $period = '30d'): array
    {
        // Obtener datos del repositorio
        $groupData = $this->attendanceRepository->getGroupAttendanceData($groupId, $period);

        $analytics = DataAnalytic::where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment')
            ->whereHasMorph('analyzable', ['IncadevUns\\CoreDomain\\Models\\Enrollment'], function ($query) use ($groupId) {
                $query->where('group_id', $groupId)
                    ->where('academic_status', 'active');
            })
            ->where('analysis_type', 'attendance')
            ->where('period', $period)
            ->get();

        logger('Analytics encontrados para grupo ' . $groupId . ', periodo ' . $period . ': ' . $analytics->count());
        foreach ($analytics as $analytic) {
            logger('Enrollment: ' . $analytic->analyzable_id . ', Rate: ' . $analytic->rate . ', Risk: ' . $analytic->risk_level);
        }

        return [
            'group_id' => $groupId,
            'analysis_period' => $period,
            'total_students' => $groupData['total_students'] ?? 0,
            'group_attendance_rate' => $groupData['group_attendance_rate'] ?? 0,
            'at_risk_students' => $groupData['at_risk_students'] ?? 0,
            'attendance_distribution' => $this->getAttendanceDistribution($analytics),
        ];
    }

    private function getAttendanceDistribution($analytics): array
    {
        return [
            'excellent' => $analytics->where('rate', '>=', 90)->count(),
            'good' => $analytics->whereBetween('rate', [80, 89.99])->count(),
            'warning' => $analytics->whereBetween('rate', [70, 79.99])->count(),
            'critical' => $analytics->where('rate', '<', 70)->count(),
        ];
    }
}
