<?php

namespace App\Domains\SupportTechnical\Repositories;

use App\Domains\SupportTechnical\Models\Ticket;
use App\Domains\SupportTechnical\Models\TicketTracking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketRepository implements TicketRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Ticket::with(['user', 'assignedTechnician']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['assigned_technician'])) {
            $query->where('assigned_technician', $filters['assigned_technician']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('creation_date', 'desc')->paginate($perPage);
    }

    public function findById(int $ticketId): ?Ticket
    {
        return Ticket::with(['user', 'assignedTechnician', 'ticketTrackings'])
            ->where('ticket_id', $ticketId)
            ->orWhere('id', $ticketId)
            ->first();
    }

    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function update(int $ticketId, array $data): Ticket
    {
        $ticket = Ticket::where('ticket_id', $ticketId)
            ->orWhere('id', $ticketId)
            ->firstOrFail();
        
        $ticket->update($data);
        
        return $ticket->fresh(['user', 'assignedTechnician']);
    }

    public function delete(int $ticketId): bool
    {
        $ticket = Ticket::where('ticket_id', $ticketId)
            ->orWhere('id', $ticketId)
            ->firstOrFail();
        
        return $ticket->delete();
    }

    public function createTracking(int $ticketId, array $data): TicketTracking
    {
        $data['ticket_id'] = $ticketId;
        $data['tracking_date'] = now();
        return TicketTracking::create($data);
    }

    public function getStats(array $filters = []): array
    {
        $query = Ticket::query();

        if (isset($filters['start_date'])) {
            $query->where('creation_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('creation_date', '<=', $filters['end_date']);
        }

        $totalTickets = $query->count();

        $byStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $byCategory = Ticket::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $avgResolutionTime = Ticket::whereNotNull('resolution_date')
            ->whereNotNull('creation_date')
            ->get()
            ->avg(function ($ticket) {
                return $ticket->creation_date->diffInHours($ticket->resolution_date);
            });

        $pendingEscalations = DB::table('escalations')
            ->where('approved', false)
            ->count();

        return [
            'total_tickets' => $totalTickets,
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_category' => $byCategory,
            'average_resolution_time_hours' => round($avgResolutionTime ?? 0, 2),
            'pending_escalations' => $pendingEscalations,
        ];
    }
}
