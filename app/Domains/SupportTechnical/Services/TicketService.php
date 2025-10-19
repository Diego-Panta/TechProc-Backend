<?php

namespace App\Domains\SupportTechnical\Services;

use App\Domains\SupportTechnical\Models\Ticket;
use App\Domains\SupportTechnical\Repositories\TicketRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    protected TicketRepositoryInterface $repository;

    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTickets(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getTicketById(int $ticketId): ?Ticket
    {
        return $this->repository->findById($ticketId);
    }

    public function createTicket(array $data): Ticket
    {
        $data['status'] = 'abierto';
        $data['creation_date'] = now();

        $ticket = $this->repository->create($data);

        if (!$ticket->ticket_id) {
            $ticket->ticket_id = $ticket->id;
            $ticket->save();
        }

        return $ticket;
    }

    public function takeTicket(int $ticketId, int $technicianId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $technicianId) {
            $ticket = $this->repository->update($ticketId, [
                'assigned_technician' => $technicianId,
                'status' => 'en_proceso',
                'assignment_date' => now(),
            ]);

            $this->repository->createTracking($ticket->id, [
                'action_type' => 'asignacion',
                'comment' => 'Ticket asignado al técnico',
                'user_id' => $technicianId,
            ]);

            return $ticket;
        });
    }

    public function updateTicketStatus(int $ticketId, string $status, ?string $notes = null): Ticket
    {
        return DB::transaction(function () use ($ticketId, $status, $notes) {
            $ticket = $this->repository->update($ticketId, ['status' => $status]);

            if ($notes) {
                $this->repository->createTracking($ticket->id, [
                    'action_type' => 'actualizacion',
                    'comment' => $notes,
                    'user_id' => $ticket->technician_id,
                ]);
            }

            return $ticket;
        });
    }

    public function resolveTicket(int $ticketId, int $technicianId, string $resolutionNotes): Ticket
    {
        return DB::transaction(function () use ($ticketId, $technicianId, $resolutionNotes) {
            $ticket = $this->repository->update($ticketId, [
                'status' => 'resuelto',
                'resolution_date' => now(),
                'assigned_technician' => $technicianId,
            ]);

            $this->repository->createTracking($ticket->id, [
                'action_type' => 'resolucion',
                'comment' => $resolutionNotes,
                'user_id' => $technicianId,
            ]);

            return $ticket;
        });
    }

    public function closeTicket(int $ticketId, string $closingNotes): Ticket
    {
        return DB::transaction(function () use ($ticketId, $closingNotes) {
            $ticket = $this->repository->findById($ticketId);

            if (!$ticket) {
                throw new \Exception('Ticket no encontrado');
            }

            $ticket = $this->repository->update($ticketId, [
                'status' => 'cerrado',
                'closing_date' => now(),
            ]);

            $this->repository->createTracking($ticket->id, [
                'action_type' => 'cierre',
                'comment' => $closingNotes,
                'user_id' => $ticket->technician_id ?? $ticket->user_id,
            ]);

            return $ticket;
        });
    }

    public function addComment(int $ticketId, string $comment, string $actionType, int $userId): void
    {
        $ticket = $this->repository->findById($ticketId);

        if (!$ticket) {
            throw new \Exception('Ticket no encontrado');
        }

        $this->repository->createTracking($ticket->id, [
            'action_type' => $actionType,
            'comment' => $comment,
            'user_id' => $userId,
        ]);
    }

    public function getStats(array $filters = []): array
    {
        return $this->repository->getStats($filters);
    }

    public function deleteTicket(int $ticketId): bool
    {
        return $this->repository->delete($ticketId);
    }
}
