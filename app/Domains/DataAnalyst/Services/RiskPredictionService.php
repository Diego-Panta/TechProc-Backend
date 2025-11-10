<?php
// app/Domains/DataAnalyst/Services/RiskPredictionService.php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Models\DataAnalytic;
use App\Domains\DataAnalyst\Repositories\StudentDataRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiskPredictionService
{
    public function __construct(
        private StudentDataRepository $studentRepository
    ) {}

    /**
     * Calcula el score de riesgo para una matrícula y guarda en DataAnalytic
     */
    public function calculateRiskScore(int $enrollmentId): array
    {
        $studentData = $this->studentRepository->getStudentIndicators($enrollmentId);

        $scores = [
            'academic' => $this->calculateAcademicRisk($studentData),
            'attendance' => $this->calculateAttendanceRisk($studentData),
            'financial' => $this->calculateFinancialRisk($studentData),
            'engagement' => $this->calculateEngagementRisk($studentData),
            'behavioral' => $this->calculateBehavioralRisk($studentData)
        ];

        $overallRisk = $this->calculateOverallRisk($scores);
        $riskLevel = $this->getRiskLevel($overallRisk);

        return [
            'risk_level' => $riskLevel,
            'risk_score' => $overallRisk,
            'component_scores' => $scores,
            'triggers' => $this->identifyRiskTriggers($scores, $studentData),
            'recommendations' => $this->generateRecommendations($scores, $studentData),
            'last_calculated' => now()->toISOString()
        ];
    }

    /**
     * Calcula y guarda el análisis de riesgo en DataAnalytic
     */
    public function calculateAndSaveRiskAnalysis(int $enrollmentId, string $period = '30d'): DataAnalytic
    {
        $riskAssessment = $this->calculateRiskScore($enrollmentId);

        // Preparar métricas específicas para riesgo de deserción
        $metrics = [
            'component_scores' => $riskAssessment['component_scores'],
            'overall_risk_score' => $riskAssessment['risk_score'],
            'risk_factors' => $this->extractRiskFactors($riskAssessment['component_scores']),
            'prediction_confidence' => $this->calculatePredictionConfidence($enrollmentId),
            'last_activity_date' => $this->getLastActivityDate($enrollmentId),
            'days_since_last_activity' => $this->getDaysSinceLastActivity($enrollmentId),
        ];

        return DataAnalytic::updateOrCreate(
            [
                'analyzable_type' => 'IncadevUns\\CoreDomain\\Models\\Enrollment',
                'analyzable_id' => $enrollmentId,
                'analysis_type' => 'risk_prediction',
                'period' => $period,
            ],
            [
                'score' => $riskAssessment['risk_score'], // Score general de riesgo (0-100)
                'rate' => $this->calculateDropoutProbability($riskAssessment['risk_score']), // Probabilidad de deserción
                'total_events' => $this->getTotalRiskEvents($enrollmentId), // Total de eventos de riesgo
                'completed_events' => $this->getCompletedInterventions($enrollmentId), // Intervenciones completadas
                'risk_level' => $riskAssessment['risk_level'],
                'metrics' => $metrics,
                'trends' => $this->calculateRiskTrends($enrollmentId),
                'patterns' => $this->identifyRiskPatterns($riskAssessment['component_scores']),
                'comparisons' => $this->compareWithGroupRisk($enrollmentId, $riskAssessment['risk_score']),
                'triggers' => $riskAssessment['triggers'],
                'recommendations' => $riskAssessment['recommendations'],
                'calculated_at' => now(),
            ]
        );
    }

    /**
     * MÉTODOS DE CÁLCULO (MANTENIENDO LA LÓGICA ORIGINAL)
     */

    /**
     * Calcula riesgo académico basado en notas y desempeño
     */
    private function calculateAcademicRisk(array $data): int
    {
        $score = 0;

        // VERIFICAR SI HAY DATOS ACADÉMICOS
        $hasAcademicData = $data['has_academic_data'] ?? false;

        if (!$hasAcademicData) {
            return 25; // Riesgo moderado por falta de datos, no alto
        }

        // Nota final (40% peso)
        $finalGrade = $data['final_grade'] ?? 0;
        if ($finalGrade > 0) { // Solo si hay nota válida
            if ($finalGrade < 11) $score += 40;
            elseif ($finalGrade < 13) $score += 25;
            elseif ($finalGrade < 15) $score += 10;
        }

        // Porcentaje de exámenes reprobados (30% peso)
        $examsTaken = $data['exams_taken'] ?? 0;
        $failedExams = $data['failed_exams'] ?? 0;

        if ($examsTaken > 0) {
            $failureRate = ($failedExams / $examsTaken) * 100;
            if ($failureRate > 60) $score += 30;
            elseif ($failureRate > 40) $score += 20;
            elseif ($failureRate > 20) $score += 10;
        }

        // Tendencia de notas (30% peso) - MEJORADA
        $gradeTrend = $data['grade_trend'] ?? 0;
        if ($gradeTrend < -1.0) $score += 30;
        elseif ($gradeTrend < -0.5) $score += 20;
        elseif ($gradeTrend < 0) $score += 10;

        return min($score, 100);
    }

    /**
     * Calcula riesgo por asistencia
     */
    private function calculateAttendanceRisk(array $data): int
    {
        $score = 0;

        // VERIFICAR SI EL GRUPO TIENE CLASES
        $hasClassActivities = $data['has_class_activities'] ?? false;
        $totalClasses = $data['total_classes'] ?? 0;
        
        if (!$hasClassActivities || $totalClasses === 0) {
            return 20; // Riesgo bajo por falta de estructura, no alto
        }

        $attendancePercentage = $data['attendance_percentage'] ?? 0;

        // Porcentaje general de asistencia (50% peso)
        if ($attendancePercentage < 60) $score += 50;
        elseif ($attendancePercentage < 70) $score += 35;
        elseif ($attendancePercentage < 80) $score += 20;
        elseif ($attendancePercentage < 90) $score += 10;

        // Ausencias recientes (30% peso)
        $recentAbsences = $data['recent_absences'] ?? 0;
        if ($recentAbsences >= 5) $score += 30;
        elseif ($recentAbsences >= 3) $score += 20;
        elseif ($recentAbsences >= 1) $score += 10;

        // Ausencias consecutivas (20% peso)
        $consecutiveAbsences = $data['consecutive_absences'] ?? 0;
        if ($consecutiveAbsences >= 3) $score += 20;
        elseif ($consecutiveAbsences >= 2) $score += 10;

        return min($score, 100);
    }

    /**
     * Calcula riesgo financiero
     */
    private function calculateFinancialRisk(array $data): int
    {
        $score = 0;

        $paymentStatus = $data['payment_status'] ?? 'pending';

        // Estado de pago (70% peso)
        if ($paymentStatus === 'overdue') $score += 70;
        elseif ($paymentStatus === 'pending') $score += 40;
        elseif ($paymentStatus === 'partial') $score += 25;

        // Historial de pagos (30% peso)
        $paymentDelays = $data['payment_delays'] ?? 0;
        if ($paymentDelays >= 3) $score += 30;
        elseif ($paymentDelays >= 1) $score += 15;

        return min($score, 100);
    }

    /**
     * Calcula riesgo por engagement y participación
     */
    private function calculateEngagementRisk(array $data): int
    {
        $score = 0;

        // VERIFICAR SI EL GRUPO TIENE ACTIVIDADES
        $hasClassActivities = $data['has_class_activities'] ?? false;
        
        if (!$hasClassActivities) {
            // Grupo nuevo sin actividades - riesgo diferente
            $score += 15; // Riesgo bajo por falta de engagement inicial
            
            // Tickets de soporte aún aplican
            $supportTickets = $data['support_tickets'] ?? 0;
            if ($supportTickets >= 3) $score += 10;
            
            return min($score, 100);
        }

        // LÓGICA ORIGINAL PARA GRUPOS CON ACTIVIDADES
        $supportTickets = $data['support_tickets'] ?? 0;
        if ($supportTickets >= 5) $score += 25;
        elseif ($supportTickets >= 3) $score += 15;
        elseif ($supportTickets >= 1) $score += 5;

        $counselingSessions = $data['counseling_sessions'] ?? 0;
        if ($counselingSessions == 0) $score += 20;
        elseif ($counselingSessions == 1) $score += 10;

        $satisfactionScore = $data['satisfaction_score'] ?? 5;
        if ($satisfactionScore < 2.0) $score += 25;
        elseif ($satisfactionScore < 3.0) $score += 15;
        elseif ($satisfactionScore < 4.0) $score += 5;

        // Inactividad reciente (30% peso) - SOLO si hay clases
        $lastActivity = $data['last_activity'] ?? null;
        if ($lastActivity) {
            $daysInactive = Carbon::parse($lastActivity)->diffInDays(now());
            if ($daysInactive > 14) $score += 30;
            elseif ($daysInactive > 7) $score += 20;
            elseif ($daysInactive > 3) $score += 10;
        } else {
            // No hay actividad registrada pero hay clases - riesgo moderado
            $score += 15;
        }

        return min($score, 100);
    }

    /**
     * Calcula riesgo comportamental
     */
    private function calculateBehavioralRisk(array $data): int
    {
        $score = 0;

        // VERIFICAR SI EL GRUPO TIENE ACTIVIDADES
        $hasClassActivities = $data['has_class_activities'] ?? false;
        
        if (!$hasClassActivities) {
            return 10; // Riesgo muy bajo para grupos nuevos
        }

        // Comportamiento en foros
        $forumParticipation = $data['forum_participation'] ?? 0;
        if ($forumParticipation === 0) $score += 20;

        // Interacciones con compañeros
        $peerInteractions = $data['peer_interactions'] ?? 0;
        if ($peerInteractions < 5) $score += 15;

        // Participación en clase
        $classParticipation = $data['class_participation'] ?? 0;
        if ($classParticipation < 3) $score += 15;

        return min($score, 50);
    }

    /**
     * Calcula el riesgo general ponderado
     */
    private function calculateOverallRisk(array $scores): int
    {
        $weights = [
            'academic' => 0.30,
            'attendance' => 0.25,
            'financial' => 0.20,
            'engagement' => 0.15,
            'behavioral' => 0.10
        ];

        $overall = 0;
        foreach ($scores as $component => $score) {
            $overall += $score * $weights[$component];
        }

        return (int) round($overall);
    }

    /**
     * Identifica los triggers específicos de riesgo
     */
    private function identifyRiskTriggers(array $scores, array $data): array
    {
        $triggers = [];

        if ($scores['academic'] >= 60) {
            $triggers[] = 'bajo_rendimiento_academico';
        }

        if ($scores['attendance'] >= 60) {
            $triggers[] = 'baja_asistencia';
        }

        if ($scores['financial'] >= 50) {
            $triggers[] = 'problemas_financieros';
        }

        if ($scores['engagement'] >= 50) {
            $triggers[] = 'bajo_compromiso';
        }

        if (($data['recent_absences'] ?? 0) >= 3) {
            $triggers[] = 'ausencias_recientes';
        }

        if (($data['final_grade'] ?? 0) < 11) {
            $triggers[] = 'nota_final_baja';
        }

        return array_unique($triggers);
    }

    /**
     * Genera recomendaciones basadas en los scores
     */
    private function generateRecommendations(array $scores, array $data): array
    {
        $recommendations = [];

        if ($scores['academic'] >= 40) {
            $recommendations[] = 'Programar tutoría académica';
            $recommendations[] = 'Revisar material de estudio';
        }

        if ($scores['attendance'] >= 40) {
            $recommendations[] = 'Contactar para verificar situación personal';
            $recommendations[] = 'Ofrecer apoyo para regularizar asistencia';
        }

        if ($scores['financial'] >= 30) {
            $recommendations[] = 'Revisar opciones de financiamiento';
            $recommendations[] = 'Coordinar plan de pagos';
        }

        if ($scores['engagement'] >= 40) {
            $recommendations[] = 'Programar cita de orientación';
            $recommendations[] = 'Incentivar participación en actividades';
        }

        if (count($recommendations) === 0) {
            $recommendations[] = 'Rendimiento satisfactorio - continuar seguimiento regular';
        }

        return $recommendations;
    }

    /**
     * Determina el nivel de riesgo basado en el score
     */
    private function getRiskLevel(int $score): string
    {
        return match (true) {
            $score >= 80 => 'critical',
            $score >= 60 => 'high',
            $score >= 40 => 'medium',
            $score >= 20 => 'low',
            default => 'none'
        };
    }

    /**
     * NUEVOS MÉTODOS PARA DataAnalytic
     */

    /**
     * Extrae factores de riesgo específicos
     */
    private function extractRiskFactors(array $componentScores): array
    {
        $factors = [];

        foreach ($componentScores as $component => $score) {
            if ($score >= 60) {
                $factors[] = "alto_riesgo_{$component}";
            } elseif ($score >= 40) {
                $factors[] = "riesgo_moderado_{$component}";
            }
        }

        return $factors;
    }

    /**
     * Calcula probabilidad de deserción basada en score de riesgo
     */
    private function calculateDropoutProbability(int $riskScore): float
    {
        // Mapeo lineal simple: 0-100 riesgo → 0-100% probabilidad
        // En producción, esto podría ser un modelo más complejo
        return min($riskScore * 0.8, 95.0); // Máximo 95% de probabilidad
    }

    /**
     * Calcula confianza de la predicción
     */
    private function calculatePredictionConfidence(int $enrollmentId): float
    {
        try {
            // Basado en la cantidad y calidad de datos disponibles
            $dataCompleteness = $this->studentRepository->getDataCompleteness($enrollmentId);

            // Obtener datos adicionales para calcular confianza
            $indicators = $this->studentRepository->getStudentIndicators($enrollmentId);

            // Factores que afectan la confianza
            $confidenceFactors = [
                'data_completeness' => $dataCompleteness / 100,
                'has_recent_activity' => $this->hasRecentActivity($enrollmentId) ? 1.0 : 0.5,
                'has_multiple_data_sources' => $this->hasMultipleDataSources($indicators) ? 1.0 : 0.7,
                'data_consistency' => $this->calculateDataConsistency($indicators),
            ];

            // Confianza base + factores ponderados
            $baseConfidence = 60.0; // Reducido de 70 para ser más conservador

            $weightedFactors = (
                $confidenceFactors['data_completeness'] * 0.4 +
                $confidenceFactors['has_recent_activity'] * 0.2 +
                $confidenceFactors['has_multiple_data_sources'] * 0.2 +
                $confidenceFactors['data_consistency'] * 0.2
            ) * 40; // Escalar a 0-40 puntos

            $totalConfidence = $baseConfidence + $weightedFactors;

            return min($totalConfidence, 95.0); // Máximo 95% de confianza

        } catch (\Exception $e) {
            // En caso de error, retornar confianza baja
            return 50.0;
        }
    }

    // MÉTODOS AUXILIARES NUEVOS
    private function hasRecentActivity(int $enrollmentId): bool
    {
        $lastActivity = DB::table('attendances as a')
            ->join('class_sessions as cs', 'a.class_session_id', '=', 'cs.id')
            ->where('a.enrollment_id', $enrollmentId)
            ->where('a.status', 'present')
            ->max('cs.start_time');

        if (!$lastActivity) {
            return false;
        }

        return Carbon::parse($lastActivity)->diffInDays(now()) <= 7;
    }

    private function hasMultipleDataSources(array $indicators): bool
    {
        $dataSources = 0;

        // Contar fuentes de datos disponibles
        if (isset($indicators['final_grade']) && $indicators['final_grade'] > 0) $dataSources++;
        if (isset($indicators['attendance_percentage']) && $indicators['attendance_percentage'] >= 0) $dataSources++;
        if (isset($indicators['payment_status'])) $dataSources++;
        if (isset($indicators['support_tickets']) && $indicators['support_tickets'] > 0) $dataSources++;
        if (isset($indicators['counseling_sessions']) && $indicators['counseling_sessions'] > 0) $dataSources++;

        return $dataSources >= 3;
    }

    private function calculateDataConsistency(array $indicators): float
    {
        $consistencyScore = 0.0;
        $checks = 0;

        // Verificar consistencia entre datos relacionados

        // 1. Consistencia notas vs asistencia
        if (isset($indicators['final_grade']) && isset($indicators['attendance_percentage'])) {
            $grade = $indicators['final_grade'];
            $attendance = $indicators['attendance_percentage'];

            // Esperar correlación positiva entre asistencia y notas
            if (($attendance > 80 && $grade >= 14) || ($attendance < 60 && $grade < 11)) {
                $consistencyScore += 1.0; // Datos consistentes
            } elseif (($attendance > 80 && $grade < 11) || ($attendance < 60 && $grade >= 14)) {
                $consistencyScore += 0.3; // Datos inconsistentes
            } else {
                $consistencyScore += 0.7; // Neutral
            }
            $checks++;
        }

        // 2. Consistencia engagement vs actividad reciente
        if (isset($indicators['support_tickets']) && isset($indicators['last_activity'])) {
            $tickets = $indicators['support_tickets'];
            $lastActivity = $indicators['last_activity'] ?? null;

            if ($lastActivity) {
                $daysInactive = Carbon::parse($lastActivity)->diffInDays(now());

                // Muchos tickets pero inactividad reciente es inconsistente
                if ($tickets >= 3 && $daysInactive > 14) {
                    $consistencyScore += 0.3;
                } else {
                    $consistencyScore += 0.8;
                }
                $checks++;
            }
        }

        return $checks > 0 ? ($consistencyScore / $checks) : 0.7;
    }

    /**
     * Obtiene fecha de última actividad
     */
    private function getLastActivityDate(int $enrollmentId): ?string
    {
        return DB::table('attendances as a')
            ->join('class_sessions as cs', 'a.class_session_id', '=', 'cs.id')
            ->where('a.enrollment_id', $enrollmentId)
            ->where('a.status', 'present')
            ->max('cs.start_time');
    }

    /**
     * Calcula días desde última actividad
     */
    private function getDaysSinceLastActivity(int $enrollmentId): int
    {
        $lastActivity = $this->getLastActivityDate($enrollmentId);

        if (!$lastActivity) {
            return 999; // Valor alto si no hay actividad
        }

        return Carbon::parse($lastActivity)->diffInDays(now());
    }

    /**
     * Obtiene total de eventos de riesgo
     */
    private function getTotalRiskEvents(int $enrollmentId): int
    {
        return DB::table('attendances as a')
            ->where('a.enrollment_id', $enrollmentId)
            ->where('a.status', 'absent')
            ->count() +
            DB::table('grades as g')
            ->where('g.enrollment_id', $enrollmentId)
            ->where('g.grade', '<', 11)
            ->count();
    }

    /**
     * Obtiene intervenciones completadas
     */
    private function getCompletedInterventions(int $enrollmentId): int
    {
        $enrollment = DB::table('enrollments')->where('id', $enrollmentId)->first();

        if (!$enrollment) {
            return 0;
        }

        return DB::table('appointments as a')
            ->where('a.student_id', $enrollment->user_id)
            ->where('a.status', 'completed')
            ->count();
    }

    /**
     * Calcula tendencias de riesgo
     */
    private function calculateRiskTrends(int $enrollmentId): array
    {
        // Obtener análisis anteriores para calcular tendencia
        $previousAnalytics = DataAnalytic::where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment')
            ->where('analyzable_id', $enrollmentId)
            ->where('analysis_type', 'risk_prediction')
            ->where('calculated_at', '>=', now()->subDays(90))
            ->orderBy('calculated_at')
            ->get();

        if ($previousAnalytics->count() < 2) {
            return ['trend' => 'stable', 'change' => 0];
        }

        $firstScore = $previousAnalytics->first()->score;
        $lastScore = $previousAnalytics->last()->score;
        $change = $lastScore - $firstScore;

        $trend = match (true) {
            $change > 10 => 'increasing',
            $change < -10 => 'decreasing',
            default => 'stable'
        };

        return [
            'trend' => $trend,
            'change' => $change,
            'previous_scores' => $previousAnalytics->pluck('score')->toArray(),
            'analysis_count' => $previousAnalytics->count()
        ];
    }

    /**
     * Identifica patrones de riesgo
     */
    private function identifyRiskPatterns(array $componentScores): array
    {
        $patterns = [];

        // CORRECCIÓN: Usar max() en lugar de array_max()
        $maxScore = !empty($componentScores) ? max($componentScores) : 0;

        // Patrón: Riesgo académico dominante
        if ($componentScores['academic'] >= 60 && $componentScores['academic'] > $maxScore * 0.8) {
            $patterns[] = 'riesgo_academico_dominante';
        }

        // Patrón: Múltiples riesgos moderados
        $moderateRisks = array_filter($componentScores, fn($score) => $score >= 40 && $score < 60);
        if (count($moderateRisks) >= 3) {
            $patterns[] = 'multiples_riesgos_moderados';
        }

        // Patrón: Riesgo financiero crítico
        if ($componentScores['financial'] >= 70) {
            $patterns[] = 'riesgo_financiero_critico';
        }

        // NUEVO: Patrón de riesgo generalizado
        $highRisks = array_filter($componentScores, fn($score) => $score >= 60);
        if (count($highRisks) >= 2) {
            $patterns[] = 'riesgo_generalizado';
        }

        // NUEVO: Patrón de bajo engagement crítico
        if ($componentScores['engagement'] >= 70) {
            $patterns[] = 'desconexion_estudiante';
        }

        return $patterns;
    }

    /**
     * Compara con el riesgo promedio del grupo
     */
    private function compareWithGroupRisk(int $enrollmentId, int $studentRiskScore): array
    {
        $enrollment = DB::table('enrollments')->where('id', $enrollmentId)->first();

        if (!$enrollment) {
            return ['comparison_available' => false];
        }

        $groupRiskAvg = DataAnalytic::where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment')
            ->whereIn('analyzable_id', function ($query) use ($enrollment) {
                $query->select('id')
                    ->from('enrollments')
                    ->where('group_id', $enrollment->group_id)
                    ->where('academic_status', 'active');
            })
            ->where('analysis_type', 'risk_prediction')
            ->avg('score');

        if (!$groupRiskAvg) {
            return ['comparison_available' => false];
        }

        $difference = $studentRiskScore - $groupRiskAvg;

        return [
            'comparison_available' => true,
            'group_average_risk' => round($groupRiskAvg, 2),
            'risk_difference' => round($difference, 2),
            'percentile' => $this->calculateRiskPercentile($enrollmentId, $studentRiskScore),
            'comparison' => $difference > 10 ? 'above_average' : ($difference < -10 ? 'below_average' : 'average')
        ];
    }

    /**
     * Calcula percentil de riesgo
     */
    private function calculateRiskPercentile(int $enrollmentId, int $studentRiskScore): int
    {
        $enrollment = DB::table('enrollments')->where('id', $enrollmentId)->first();

        if (!$enrollment) {
            return 50;
        }

        $groupRisks = DataAnalytic::where('analyzable_type', 'IncadevUns\\CoreDomain\\Models\\Enrollment')
            ->whereIn('analyzable_id', function ($query) use ($enrollment) {
                $query->select('id')
                    ->from('enrollments')
                    ->where('group_id', $enrollment->group_id)
                    ->where('academic_status', 'active');
            })
            ->where('analysis_type', 'risk_prediction')
            ->pluck('score')
            ->toArray();

        if (empty($groupRisks)) {
            return 50;
        }

        sort($groupRisks);
        $position = array_search($studentRiskScore, $groupRisks);

        if ($position === false) {
            return 50;
        }

        return (int) round(($position / count($groupRisks)) * 100);
    }

    /**
     * Procesa riesgo para todos los estudiantes activos
     */
    public function calculateRiskForAllActiveStudents(string $period = '30d'): array
    {
        $activeEnrollments = DB::table('enrollments as e')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->where('g.status', 'active')
            ->where('e.academic_status', 'active')
            ->pluck('e.id');

        $results = [
            'total_processed' => 0,
            'critical_risk' => 0,
            'high_risk' => 0,
            'medium_risk' => 0,
            'low_risk' => 0,
            'no_risk' => 0
        ];

        foreach ($activeEnrollments as $enrollmentId) {
            $riskAnalysis = $this->calculateAndSaveRiskAnalysis($enrollmentId, $period);
            $results['total_processed']++;

            switch ($riskAnalysis->risk_level) {
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
        }

        return $results;
    }
}
