<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\SupportSecurity\Models\SecurityLog;
use App\Domains\SupportSecurity\Models\BlockedIp;
use App\Domains\SupportSecurity\Models\SecurityAlert;
use App\Domains\SupportSecurity\Models\Incident;
use Illuminate\Support\Facades\DB;

class SecurityReportRepository
{
    public function getSecurityAnalysis(array $filters = [])
    {
        // Total eventos de seguridad
        $securityLogsQuery = SecurityLog::query();
        $blockedIpsQuery = BlockedIp::query();
        $securityAlertsQuery = SecurityAlert::query();
        $incidentsQuery = Incident::query();

        // Aplicar filtros de fecha
        if (!empty($filters['start_date'])) {
            $securityLogsQuery->whereDate('event_date', '>=', $filters['start_date']);
            $blockedIpsQuery->whereDate('block_date', '>=', $filters['start_date']);
            $securityAlertsQuery->whereDate('detection_date', '>=', $filters['start_date']);
            $incidentsQuery->whereDate('report_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $securityLogsQuery->whereDate('event_date', '<=', $filters['end_date']);
            $blockedIpsQuery->whereDate('block_date', '<=', $filters['end_date']);
            $securityAlertsQuery->whereDate('detection_date', '<=', $filters['end_date']);
            $incidentsQuery->whereDate('report_date', '<=', $filters['end_date']);
        }

        // Eventos por tipo
        $eventsByType = SecurityLog::select('event_type', DB::raw('COUNT(*) as count'))
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '<=', $filters['end_date']);
            })
            ->groupBy('event_type')
            ->get()
            ->pluck('count', 'event_type')
            ->toArray();

        // IPs bloqueadas
        $totalBlockedIps = $blockedIpsQuery->count();
        $activeBlockedIps = (clone $blockedIpsQuery)->where('active', true)->count();
        $blockedIpsThisPeriod = BlockedIp::when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('block_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('block_date', '<=', $filters['end_date']);
            })
            ->count();

        // Alertas por severidad
        $alertsBySeverity = SecurityAlert::select('severity', DB::raw('COUNT(*) as count'))
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('detection_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('detection_date', '<=', $filters['end_date']);
            })
            ->groupBy('severity')
            ->get()
            ->pluck('count', 'severity')
            ->toArray();

        // Incidentes
        $totalIncidents = $incidentsQuery->count();
        $resolvedIncidents = (clone $incidentsQuery)->where('status', 'resolved')->count();
        $inProgressIncidents = (clone $incidentsQuery)->where('status', 'in_progress')->count();

        // Tasa de logins fallidos
        $totalLogins = SecurityLog::whereIn('event_type', ['login_success', 'login_failure'])
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '<=', $filters['end_date']);
            })
            ->count();

        $failedLogins = SecurityLog::where('event_type', 'login_failure')
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '<=', $filters['end_date']);
            })
            ->count();

        $failedLoginRate = $totalLogins > 0 ? ($failedLogins / $totalLogins) * 100 : 0;

        // IPs con más intentos
        $topThreatIps = SecurityLog::select('source_ip', DB::raw('COUNT(*) as attempt_count'))
            ->where('event_type', 'login_failure')
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('event_date', '<=', $filters['end_date']);
            })
            ->groupBy('source_ip')
            ->orderByDesc('attempt_count')
            ->limit(10)
            ->get()
            ->map(function ($ip) {
                $blocked = BlockedIp::where('ip_address', $ip->source_ip)
                    ->where('active', true)
                    ->exists();
                
                return [
                    'ip_address' => $ip->source_ip,
                    'attempt_count' => $ip->attempt_count,
                    'blocked' => $blocked
                ];
            });

        return [
            'total_security_events' => $securityLogsQuery->count(),
            'by_event_type' => $eventsByType,
            'blocked_ips' => [
                'total' => $totalBlockedIps,
                'active' => $activeBlockedIps,
                'this_period' => $blockedIpsThisPeriod
            ],
            'security_alerts' => [
                'total' => $securityAlertsQuery->count(),
                'by_severity' => $alertsBySeverity
            ],
            'incidents' => [
                'total' => $totalIncidents,
                'resolved' => $resolvedIncidents,
                'in_progress' => $inProgressIncidents
            ],
            'failed_login_rate' => round($failedLoginRate, 2),
            'top_threat_ips' => $topThreatIps
        ];
    }

    public function getSecurityEvents(array $filters = [])
    {
        $query = SecurityLog::with(['user'])
            ->select('security_logs.*');

        // Aplicar filtros
        if (!empty($filters['start_date'])) {
            $query->whereDate('event_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('event_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['event_type'])) {
            $query->where('event_type', 'ILIKE', "%{$filters['event_type']}%");
        }

        if (!empty($filters['ip_address'])) {
            $query->where('source_ip', $filters['ip_address']);
        }

        return $query->orderBy('event_date', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    public function getSecurityAlerts(array $filters = [])
    {
        $query = SecurityAlert::with(['blockedIp', 'incidents'])
            ->select('security_alerts.*');

        // Aplicar filtros
        if (!empty($filters['start_date'])) {
            $query->whereDate('detection_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('detection_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('detection_date', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }
}