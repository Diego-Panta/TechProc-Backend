<?php

namespace App\Domains\SupportTechnical\Repositories;

use IncadevUns\CoreDomain\Models\Ticket;
use IncadevUns\CoreDomain\Models\TicketReply;
use IncadevUns\CoreDomain\Models\ReplyAttachment;
use IncadevUns\CoreDomain\Enums\TicketStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketRepository implements TicketRepositoryInterface
{
    /**
     * Get all tickets with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Ticket::with(['user', 'replies' => function ($query) {
            $query->with('user')->latest()->take(1);
        }]);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'updated_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Add replies count
        $query->withCount('replies');

        return $query->paginate($perPage);
    }

    /**
     * Find a ticket by ID
     */
    public function findById(int $ticketId): ?Ticket
    {
        return Ticket::with(['user', 'replies.user', 'replies.attachments'])
            ->find($ticketId);
    }

    /**
     * Create a new ticket
     */
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    /**
     * Update an existing ticket
     */
    public function update(int $ticketId, array $data): Ticket
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->update($data);
        
        return $ticket->fresh(['user', 'replies']);
    }

    /**
     * Close a ticket
     */
    public function close(int $ticketId): Ticket
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->update(['status' => TicketStatus::Closed]);
        
        return $ticket->fresh();
    }

    /**
     * Reopen a ticket
     */
    public function reopen(int $ticketId): Ticket
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->update(['status' => TicketStatus::Open]);
        
        return $ticket->fresh();
    }

    /**
     * Create a reply for a ticket
     */
    public function createReply(int $ticketId, array $data): TicketReply
    {
        $data['ticket_id'] = $ticketId;
        return TicketReply::create($data);
    }

    /**
     * Update a reply
     */
    public function updateReply(int $replyId, array $data): TicketReply
    {
        $reply = TicketReply::findOrFail($replyId);
        $reply->update($data);
        
        return $reply->fresh(['user', 'attachments']);
    }

    /**
     * Delete a reply
     */
    public function deleteReply(int $replyId): bool
    {
        $reply = TicketReply::findOrFail($replyId);
        
        // Delete associated attachments
        foreach ($reply->attachments as $attachment) {
            $this->deleteAttachment($attachment->id);
        }
        
        return $reply->delete();
    }

    /**
     * Find a reply by ID
     */
    public function findReplyById(int $replyId): ?TicketReply
    {
        return TicketReply::with(['user', 'ticket', 'attachments'])->find($replyId);
    }

    /**
     * Create an attachment for a reply
     */
    public function createAttachment(int $replyId, array $data): ReplyAttachment
    {
        $data['ticket_reply_id'] = $replyId;
        return ReplyAttachment::create($data);
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        $attachment = ReplyAttachment::findOrFail($attachmentId);
        
        // Delete the physical file
        if (\Storage::disk('public')->exists($attachment->path)) {
            \Storage::disk('public')->delete($attachment->path);
        }
        
        return $attachment->delete();
    }

    /**
     * Find an attachment by ID
     */
    public function findAttachmentById(int $attachmentId): ?ReplyAttachment
    {
        return ReplyAttachment::with('ticketReply.ticket')->find($attachmentId);
    }

    /**
     * Get statistics
     */
    public function getStats(array $filters = []): array
    {
        $query = Ticket::query();

        // Apply period filter
        $period = $filters['period'] ?? 'month';
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $queryForPeriod = (clone $query)->where('created_at', '>=', $startDate);

        // Total tickets
        $totalTickets = $query->count();
        
        // By status
        $openTickets = $query->where('status', TicketStatus::Open)->count();
        $pendingTickets = $query->where('status', TicketStatus::Pending)->count();
        $closedTickets = $query->where('status', TicketStatus::Closed)->count();

        // By priority
        $byPriority = $query->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // By type
        $byType = $query->select('type', DB::raw('count(*) as count'))
            ->whereNotNull('type')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Tickets created today
        $ticketsCreatedToday = Ticket::whereDate('created_at', today())->count();

        // Tickets resolved today (closed today)
        $ticketsResolvedToday = Ticket::where('status', TicketStatus::Closed)
            ->whereDate('updated_at', today())
            ->count();

        // Average response time (time until first reply by support)
        $avgResponseTime = $this->calculateAverageResponseTime();

        // Average resolution time (time until ticket closed)
        $avgResolutionTime = $this->calculateAverageResolutionTime();

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'pending_tickets' => $pendingTickets,
            'closed_tickets' => $closedTickets,
            'by_priority' => $byPriority,
            'by_type' => $byType,
            'average_response_time' => $avgResponseTime,
            'average_resolution_time' => $avgResolutionTime,
            'tickets_created_today' => $ticketsCreatedToday,
            'tickets_resolved_today' => $ticketsResolvedToday,
        ];
    }

    /**
     * Calculate average response time
     */
    private function calculateAverageResponseTime(): string
    {
        $tickets = Ticket::with('replies')->get();
        $responseTimes = [];

        foreach ($tickets as $ticket) {
            if ($ticket->replies->count() >= 2) {
                $firstReply = $ticket->replies->first();
                $secondReply = $ticket->replies->skip(1)->first();
                
                if ($firstReply && $secondReply) {
                    $responseTimes[] = $firstReply->created_at->diffInMinutes($secondReply->created_at);
                }
            }
        }

        if (empty($responseTimes)) {
            return 'N/A';
        }

        $avgMinutes = array_sum($responseTimes) / count($responseTimes);
        $hours = floor($avgMinutes / 60);
        $minutes = round($avgMinutes % 60);

        return "{$hours} hours {$minutes} minutes";
    }

    /**
     * Calculate average resolution time
     */
    private function calculateAverageResolutionTime(): string
    {
        $closedTickets = Ticket::where('status', TicketStatus::Closed)->get();
        
        if ($closedTickets->isEmpty()) {
            return 'N/A';
        }

        $resolutionTimes = $closedTickets->map(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->updated_at);
        });

        $avgMinutes = $resolutionTimes->avg();
        $hours = floor($avgMinutes / 60);
        $minutes = round($avgMinutes % 60);

        return "{$hours} hours {$minutes} minutes";
    }
}
