<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Models\Course;
use App\Domains\Lms\Models\Enrollment;
use App\Domains\Lms\Models\Attendance;
use App\Domains\Lms\Models\FinalGrade;
use App\Domains\SupportTechnical\Models\Ticket;
// use App\Domains\SupportSecurity\Models\SecurityAlert; // DEPRECATED: SupportSecurity eliminado, usar Security
// use App\Domains\SupportSecurity\Models\BlockedIp; // DEPRECATED: SupportSecurity eliminado, usar Security
use App\Domains\DataAnalyst\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function getDashboardData(array $filters = [])
    {
        return [
            'students' => $this->getStudentMetrics($filters),
            'courses' => $this->getCourseMetrics($filters),
            'attendance' => $this->getAttendanceMetrics($filters),
            'performance' => $this->getPerformanceMetrics($filters),
            'revenue' => $this->getRevenueMetrics($filters),
            'support' => $this->getSupportMetrics($filters),
            'security' => $this->getSecurityMetrics($filters),
            'recent_activities' => $this->getRecentActivities($filters),
            // Agregar datos adicionales REALES
            'student_company_distribution' => $this->getStudentCompanyDistribution($filters),
            'revenue_sources_distribution' => $this->getRevenueSourcesDistribution($filters),
            'monthly_revenue_trend' => $this->getMonthlyRevenueTrend($filters)
        ];
    }

    private function getStudentMetrics(array $filters)
    {
        $baseQuery = Student::query();
        
        // Aplicar filtros
        $this->applyDateFilters($baseQuery, $filters);
        $this->applyCompanyFilter($baseQuery, $filters);

        $total = $baseQuery->count();
        $active = (clone $baseQuery)->where('status', 'active')->count();
        
        // Calcular tasa de crecimiento (último mes vs mes anterior)
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        
        $currentMonthCount = (clone $baseQuery)
            ->whereDate('created_at', '>=', $currentMonth)
            ->count();
            
        $previousMonthCount = (clone $baseQuery)
            ->whereDate('created_at', '>=', $previousMonth)
            ->whereDate('created_at', '<', $currentMonth)
            ->count();
            
        $growthRate = $previousMonthCount > 0 
            ? (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100 
            : ($currentMonthCount > 0 ? 100 : 0);

        return [
            'total' => $total,
            'active' => $active,
            'growth_rate' => round($growthRate, 1)
        ];
    }

    /**
     * DISTRIBUCIÓN REAL de estudiantes por empresa
     */
    public function getStudentCompanyDistribution(array $filters)
    {
        $query = Student::join('companies', 'students.company_id', '=', 'companies.id')
            ->selectRaw('companies.id as company_id, companies.name as company_name, COUNT(*) as student_count')
            ->groupBy('companies.id', 'companies.name')
            ->orderBy('student_count', 'desc');

        // Aplicar filtros
        if (!empty($filters['company_id'])) {
            $query->where('companies.id', $filters['company_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('students.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('students.created_at', '<=', $filters['end_date']);
        }

        return $query->get()->toArray();
    }

    /**
     * DISTRIBUCIÓN REAL de fuentes de ingresos
     */
    public function getRevenueSourcesDistribution(array $filters)
    {
        // Primero obtener el total de ingresos para calcular porcentajes
        $totalRevenue = Payment::where('status', 'Completed')
            ->when(!empty($filters['start_date']), function($q) use ($filters) {
                $q->whereDate('payment_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function($q) use ($filters) {
                $q->whereDate('payment_date', '<=', $filters['end_date']);
            })
            ->sum('amount') ?? 1; // Evitar división por cero

        $distribution = Payment::join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('revenue_sources', 'invoices.revenue_source_id', '=', 'revenue_sources.id')
            ->where('payments.status', 'Completed')
            ->selectRaw('
                revenue_sources.id as source_id, 
                revenue_sources.name as source_name, 
                SUM(payments.amount) as amount'
            )
            ->groupBy('revenue_sources.id', 'revenue_sources.name')
            ->orderBy('amount', 'desc');

        // Aplicar filtros de fecha
        if (!empty($filters['start_date'])) {
            $distribution->whereDate('payments.payment_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $distribution->whereDate('payments.payment_date', '<=', $filters['end_date']);
        }

        return $distribution->get()
            ->map(function($item) use ($totalRevenue) {
                return [
                    'source_id' => $item->source_id,
                    'source_name' => $item->source_name,
                    'amount' => (float) $item->amount,
                    'percentage' => $totalRevenue > 0 ? round(($item->amount / $totalRevenue) * 100, 1) : 0
                ];
            })
            ->toArray();
    }

    /**
     * TENDENCIA MENSUAL REAL de ingresos
     */
    public function getMonthlyRevenueTrend(array $filters)
    {
        $query = Payment::where('status', 'Completed')
            ->selectRaw('
                DATE_FORMAT(payment_date, "%Y-%m") as month,
                SUM(amount) as revenue'
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->limit(12); // Últimos 12 meses

        // Aplicar filtros de fecha
        if (!empty($filters['start_date'])) {
            $query->whereDate('payment_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('payment_date', '<=', $filters['end_date']);
        }

        return $query->get()
            ->map(function($item) {
                return [
                    'month' => $item->month,
                    'revenue' => (float) $item->revenue
                ];
            })
            ->toArray();
    }

    private function getCourseMetrics(array $filters)
    {
        $baseQuery = Course::query();
        
        // Aplicar filtros de fecha si es necesario
        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        $total = $baseQuery->count();
        $active = (clone $baseQuery)->where('status', true)->count();
        
        // Total de matrículas (usando enrollments directamente)
        $enrollmentQuery = Enrollment::query();
        $this->applyDateFilters($enrollmentQuery, $filters, 'enrollment_date');
        $this->applyAcademicPeriodFilter($enrollmentQuery, $filters);
        
        $totalEnrollments = $enrollmentQuery->count();

        return [
            'total' => $total,
            'active' => $active,
            'total_enrollments' => $totalEnrollments
        ];
    }

    private function getAttendanceMetrics(array $filters)
    {
        // Consulta SIMPLE solo con la tabla attendances
        $attendanceQuery = Attendance::query();
        
        // Aplicar filtros - usar created_at
        $this->applyDateFilters($attendanceQuery, $filters, 'created_at');

        $totalRecords = $attendanceQuery->count();
        $attendedRecords = (clone $attendanceQuery)->where('attended', true)->count();
        
        $averageRate = $totalRecords > 0 ? ($attendedRecords / $totalRecords) * 100 : 0;
        
        // Calcular tendencia (última semana vs semana anterior)
        $currentWeek = now()->startOfWeek();
        $previousWeek = now()->subWeek()->startOfWeek();
        
        $currentWeekAttended = Attendance::whereDate('created_at', '>=', $currentWeek)
            ->where('attended', true)
            ->count();
        $currentWeekTotal = Attendance::whereDate('created_at', '>=', $currentWeek)->count();
        $currentWeekRate = $currentWeekTotal > 0 ? ($currentWeekAttended / $currentWeekTotal) * 100 : 0;
            
        $previousWeekAttended = Attendance::whereDate('created_at', '>=', $previousWeek)
            ->whereDate('created_at', '<', $currentWeek)
            ->where('attended', true)
            ->count();
        $previousWeekTotal = Attendance::whereDate('created_at', '>=', $previousWeek)
            ->whereDate('created_at', '<', $currentWeek)
            ->count();
        $previousWeekRate = $previousWeekTotal > 0 ? ($previousWeekAttended / $previousWeekTotal) * 100 : 0;
            
        $trend = $currentWeekRate >= $previousWeekRate ? 'up' : 'down';

        return [
            'average_rate' => round($averageRate, 1),
            'trend' => $trend
        ];
    }

    private function getPerformanceMetrics(array $filters)
    {
        // Consulta directa a final_grades
        $gradesQuery = FinalGrade::query();
        
        // Aplicar filtros
        $this->applyDateFilters($gradesQuery, $filters, 'calculation_date');

        $averageGrade = $gradesQuery->avg('final_grade') ?? 0;
        
        $passingCount = (clone $gradesQuery)
            ->where('program_status', 'Passed')
            ->count();
            
        $totalCount = $gradesQuery->count();
        $passingRate = $totalCount > 0 ? ($passingCount / $totalCount) * 100 : 0;

        return [
            'average_grade' => round($averageGrade, 1),
            'passing_rate' => round($passingRate, 1)
        ];
    }

    private function getRevenueMetrics(array $filters)
    {
        $paymentQuery = Payment::query()
            ->where('status', 'Completed');
        
        $this->applyDateFilters($paymentQuery, $filters, 'payment_date');

        $totalRevenue = $paymentQuery->sum('amount') ?? 0;
        
        // Calcular tasa de crecimiento (mes actual vs mes anterior)
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();
        
        $currentMonthRevenue = Payment::where('status', 'Completed')
            ->whereDate('payment_date', '>=', $currentMonth)
            ->sum('amount') ?? 0;
            
        $previousMonthRevenue = Payment::where('status', 'Completed')
            ->whereDate('payment_date', '>=', $previousMonth)
            ->whereDate('payment_date', '<', $currentMonth)
            ->sum('amount') ?? 0;
            
        $growthRate = $previousMonthRevenue > 0 
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 
            : ($currentMonthRevenue > 0 ? 100 : 0);

        return [
            'total' => round($totalRevenue, 2),
            'growth_rate' => round($growthRate, 1)
        ];
    }

    private function getSupportMetrics(array $filters)
    {
        $ticketQuery = Ticket::query();
        $this->applyDateFilters($ticketQuery, $filters, 'creation_date');

        // Usar estados reales de tu tabla tickets
        $openTickets = (clone $ticketQuery)
            ->whereIn('status', ['abierto', 'en_proceso', 'asignado'])
            ->count();
        
        // Calcular tiempo promedio de resolución (en horas) para tickets cerrados - CORREGIDO para MySQL
        $resolvedTickets = Ticket::where('status', 'cerrado')
            ->whereNotNull('resolution_date')
            ->whereNotNull('creation_date');
            
        $this->applyDateFilters($resolvedTickets, $filters, 'creation_date');
            
        // CORRECCIÓN: Usar TIMESTAMPDIFF para MySQL en lugar de EXTRACT(EPOCH) de PostgreSQL
        $averageResolutionTime = $resolvedTickets->avg(
            DB::raw("TIMESTAMPDIFF(HOUR, creation_date, resolution_date)")
        ) ?? 0;

        return [
            'open_tickets' => $openTickets,
            'average_resolution_time_hours' => round($averageResolutionTime, 1)
        ];
    }

    private function getSecurityMetrics(array $filters)
    {
        $securityAlertQuery = SecurityAlert::query();
        $this->applyDateFilters($securityAlertQuery, $filters, 'detection_date');
        
        $activeAlerts = SecurityAlert::where('status', 'new')->count();
        $blockedIps = BlockedIp::where('active', true)->count();

        return [
            'active_alerts' => $activeAlerts,
            'blocked_ips' => $blockedIps
        ];
    }

    private function getRecentActivities(array $filters)
    {
        // Obtener actividades recientes (últimas 5)
        $activities = [];
        
        // Nuevas matrículas hoy
        $todayEnrollments = Enrollment::whereDate('enrollment_date', today())->count();
        if ($todayEnrollments > 0) {
            $activities[] = [
                'type' => 'enrollment',
                'description' => "{$todayEnrollments} nuevas matrículas hoy",
                'timestamp' => now()->toIso8601String()
            ];
        }
        
        // Tickets nuevos
        $todayTickets = Ticket::whereDate('creation_date', today())->count();
        if ($todayTickets > 0) {
            $activities[] = [
                'type' => 'ticket',
                'description' => "{$todayTickets} nuevos tickets de soporte",
                'timestamp' => now()->toIso8601String()
            ];
        }
        
        // Pagos procesados
        $todayPayments = Payment::whereDate('payment_date', today())
            ->where('status', 'Completed')
            ->count();
        if ($todayPayments > 0) {
            $activities[] = [
                'type' => 'payment',
                'description' => "{$todayPayments} pagos procesados hoy",
                'timestamp' => now()->toIso8601String()
            ];
        }

        // Nuevos estudiantes hoy
        $todayStudents = Student::whereDate('created_at', today())->count();
        if ($todayStudents > 0) {
            $activities[] = [
                'type' => 'student',
                'description' => "{$todayStudents} nuevos estudiantes registrados",
                'timestamp' => now()->toIso8601String()
            ];
        }

        // Alertas de seguridad hoy
        $todayAlerts = SecurityAlert::whereDate('detection_date', today())->count();
        if ($todayAlerts > 0) {
            $activities[] = [
                'type' => 'security',
                'description' => "{$todayAlerts} nuevas alertas de seguridad",
                'timestamp' => now()->toIso8601String()
            ];
        }

        return array_slice($activities, 0, 5);
    }

    // Métodos auxiliares para aplicar filtros
    private function applyDateFilters($query, array $filters, $dateField = 'created_at')
    {
        if (!empty($filters['start_date'])) {
            $query->whereDate($dateField, '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate($dateField, '<=', $filters['end_date']);
        }
    }

    private function applyCompanyFilter($query, array $filters)
    {
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
    }

    private function applyAcademicPeriodFilter($query, array $filters)
    {
        if (!empty($filters['academic_period_id'])) {
            $query->where('academic_period_id', $filters['academic_period_id']);
        }
    }
}