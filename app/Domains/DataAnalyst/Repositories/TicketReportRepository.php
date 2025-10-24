<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\SupportTechnical\Models\Ticket;
use App\Domains\SupportTechnical\Models\Escalation;
use App\Domains\Administrator\Models\Employee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketReportRepository
{
    public function getTicketsAnalysis(array $filters = [])
    {
        $query = Ticket::with([
            'assignedTechnician.user',
            'escalations',
            'assignedTechnician' => function ($query) {
                $query->with('user');
            }
        ])
            ->select('tickets.*');

        // Aplicar filtros
        if (!empty($filters['start_date'])) {
            $query->whereDate('tickets.creation_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('tickets.creation_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['category'])) {
            $query->where('tickets.category', 'ILIKE', "%{$filters['category']}%");
        }

        if (!empty($filters['priority'])) {
            $query->where('tickets.priority', $filters['priority']);
        }

        if (!empty($filters['status'])) {
            $query->where('tickets.status', $filters['status']);
        }

        if (!empty($filters['technician_id'])) {
            $query->where('tickets.assigned_technician', $filters['technician_id']);
        }

        return $query->orderBy('tickets.creation_date', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function getTicketStatistics(array $filters = [])
    {
        $baseQuery = Ticket::query();

        // Filtros de fecha
        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('creation_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('creation_date', '<=', $filters['end_date']);
        }

        // Filtro por categoría
        if (!empty($filters['category'])) {
            $baseQuery->where('category', 'ILIKE', "%{$filters['category']}%");
        }

        $totalTickets = $baseQuery->count();

        // Estadísticas por estado - corregido para PostgreSQL
        $byStatus = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Estadísticas por prioridad
        $byPriority = (clone $baseQuery)
            ->select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();

        // Estadísticas por categoría
        $byCategory = (clone $baseQuery)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        // Métricas de resolución
        $resolutionMetrics = $this->calculateResolutionMetrics($baseQuery);

        // Performance de técnicos
        $technicianPerformance = $this->getTechnicianPerformance($filters);

        // Tasa de escalación
        $escalationRate = $this->calculateEscalationRate($filters);

        return [
            'total_tickets' => $totalTickets,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_category' => $byCategory,
            'resolution_metrics' => $resolutionMetrics,
            'technician_performance' => $technicianPerformance,
            'escalation_rate' => $escalationRate,
        ];
    }

    private function calculateResolutionMetrics($baseQuery)
    {
        $resolvedTickets = (clone $baseQuery)
            ->whereIn('status', ['resuelto', 'cerrado'])
            ->whereNotNull('resolution_date')
            ->whereNotNull('creation_date')
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return [
                'average_resolution_time_hours' => 0,
                'median_resolution_time_hours' => 0,
                'first_response_time_hours' => 0,
            ];
        }

        $resolutionTimes = $resolvedTickets->map(function ($ticket) {
            return $ticket->creation_date->diffInHours($ticket->resolution_date);
        });

        // Calcular tiempo promedio de primera respuesta
        $firstResponseTime = $resolvedTickets
            ->filter(function ($ticket) {
                return $ticket->assignment_date && $ticket->creation_date;
            })
            ->map(function ($ticket) {
                return $ticket->creation_date->diffInHours($ticket->assignment_date);
            })
            ->avg();

        return [
            'average_resolution_time_hours' => round($resolutionTimes->avg(), 1),
            'median_resolution_time_hours' => round($this->calculateMedian($resolutionTimes->toArray()), 1),
            'first_response_time_hours' => round($firstResponseTime ?: 0, 1),
        ];
    }

    private function getTechnicianPerformance(array $filters)
    {
        return Employee::with(['user', 'assignedTickets' => function ($query) use ($filters) {
            if (!empty($filters['start_date'])) {
                $query->whereDate('creation_date', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $query->whereDate('creation_date', '<=', $filters['end_date']);
            }
        }])
            ->whereHas('assignedTickets')
            ->get()
            ->map(function ($technician) {
                $resolvedTickets = $technician->assignedTickets
                    ->whereIn('status', ['resuelto', 'cerrado'])
                    ->whereNotNull('resolution_date')
                    ->whereNotNull('creation_date');

                $averageResolutionTime = $resolvedTickets->isEmpty() ? 0 :
                    $resolvedTickets->avg(function ($ticket) {
                        return $ticket->creation_date->diffInHours($ticket->resolution_date);
                    });

                return [
                    'technician_id' => $technician->id,
                    'technician_name' => $technician->user ? ($technician->user->first_name . ' ' . $technician->user->last_name) : 'N/A',
                    'tickets_resolved' => $resolvedTickets->count(),
                    'average_resolution_time_hours' => round($averageResolutionTime, 1),
                ];
            })
            ->where('tickets_resolved', '>', 0)
            ->sortByDesc('tickets_resolved')
            ->values()
            ->toArray();
    }

    private function calculateEscalationRate(array $filters)
    {
        $totalTickets = Ticket::query();
        $escalatedTickets = Escalation::query();

        // Aplicar filtros de fecha
        if (!empty($filters['start_date'])) {
            $totalTickets->whereDate('creation_date', '>=', $filters['start_date']);
            $escalatedTickets->whereDate('escalation_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $totalTickets->whereDate('creation_date', '<=', $filters['end_date']);
            $escalatedTickets->whereDate('escalation_date', '<=', $filters['end_date']);
        }

        $totalCount = $totalTickets->count();
        $escalatedCount = $escalatedTickets->distinct('ticket_id')->count('ticket_id');

        if ($totalCount === 0) {
            return 0;
        }

        return round(($escalatedCount / $totalCount) * 100, 1);
    }

    private function calculateMedian(array $numbers)
    {
        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);

        if ($count % 2) {
            return $numbers[$middle];
        }

        return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
    }

    public function getCategoryStatistics(array $filters = [])
    {
        $query = Ticket::query();

        // Aplicar filtros de fecha
        if (!empty($filters['start_date'])) {
            $query->whereDate('creation_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('creation_date', '<=', $filters['end_date']);
        }

        // Primero obtenemos las categorías con sus conteos básicos
        $categories = $query->select('category')
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw("SUM(CASE WHEN status IN ('abierto', 'en_proceso') THEN 1 ELSE 0 END) as open_tickets")
            ->selectRaw("SUM(CASE WHEN status IN ('resuelto', 'cerrado') THEN 1 ELSE 0 END) as resolved_tickets")
            ->selectRaw("AVG(CASE WHEN status IN ('resuelto', 'cerrado') AND resolution_date IS NOT NULL THEN EXTRACT(EPOCH FROM (resolution_date - creation_date))/3600 ELSE NULL END) as average_resolution_time")
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('total_tickets', 'desc')
            ->get();

        // Ahora obtenemos los conteos de escalaciones por categoría
        $escalationCounts = DB::table('tickets')
            ->join('escalations', 'tickets.id', '=', 'escalations.ticket_id')
            ->select('tickets.category', DB::raw('COUNT(escalations.id) as escalation_count'))
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('tickets.creation_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('tickets.creation_date', '<=', $filters['end_date']);
            })
            ->whereNotNull('tickets.category')
            ->groupBy('tickets.category')
            ->get()
            ->keyBy('category');

        // Combinamos los resultados
        return $categories->map(function ($category) use ($escalationCounts) {
            $escalationCount = $escalationCounts->get($category->category);

            return [
                'category' => $category->category,
                'total_tickets' => $category->total_tickets,
                'open_tickets' => $category->open_tickets,
                'resolved_tickets' => $category->resolved_tickets,
                'average_resolution_time' => round($category->average_resolution_time ?? 0, 1),
                'escalation_count' => $escalationCount ? $escalationCount->escalation_count : 0,
            ];
        });
    }

    public function getTechnicianRanking(array $filters = [])
    {
        $limit = $filters['limit'] ?? 10;

        $technicians = Employee::with(['user'])
            ->whereHas('assignedTickets')
            ->withCount(['assignedTickets as total_tickets' => function ($query) use ($filters) {
                if (!empty($filters['start_date'])) {
                    $query->whereDate('creation_date', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $query->whereDate('creation_date', '<=', $filters['end_date']);
                }
            }])
            ->withCount(['assignedTickets as resolved_tickets' => function ($query) use ($filters) {
                $query->whereIn('status', ['resuelto', 'cerrado']);
                if (!empty($filters['start_date'])) {
                    $query->whereDate('creation_date', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $query->whereDate('creation_date', '<=', $filters['end_date']);
                }
            }])
            ->withCount(['escalationDestinies as escalation_count' => function ($query) use ($filters) {
                if (!empty($filters['start_date'])) {
                    $query->whereDate('escalation_date', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $query->whereDate('escalation_date', '<=', $filters['end_date']);
                }
            }])
            ->get()
            ->map(function ($technician, $index) {
                $resolutionRate = $technician->total_tickets > 0
                    ? round(($technician->resolved_tickets / $technician->total_tickets) * 100, 1)
                    : 0;

                // Calcular tiempo promedio de resolución para este técnico
                $resolvedTickets = $technician->assignedTickets()
                    ->whereIn('status', ['resuelto', 'cerrado'])
                    ->whereNotNull('resolution_date')
                    ->get();

                $averageResolutionTime = $resolvedTickets->isEmpty() ? 0 :
                    $resolvedTickets->avg(function ($ticket) {
                        return $ticket->creation_date->diffInHours($ticket->resolution_date);
                    });

                return [
                    'rank' => $index + 1,
                    'technician_id' => $technician->id,
                    'technician_name' => $technician->user->first_name . ' ' . $technician->user->last_name,
                    'total_tickets' => $technician->total_tickets,
                    'resolved_tickets' => $technician->resolved_tickets,
                    'resolution_rate' => $resolutionRate,
                    'average_resolution_time' => round($averageResolutionTime, 1),
                    'escalation_count' => $technician->escalation_count,
                ];
            })
            ->where('total_tickets', '>', 0)
            ->sortByDesc('resolution_rate')
            ->take($limit)
            ->values();

        return $technicians;
    }
}
