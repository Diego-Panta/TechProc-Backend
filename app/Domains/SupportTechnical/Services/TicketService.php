<?php

namespace App\Domains\SupportTechnical\Services;

use IncadevUns\CoreDomain\Models\Ticket;
use IncadevUns\CoreDomain\Enums\TicketStatus;
use App\Domains\SupportTechnical\Repositories\TicketRepositoryInterface;
use App\Domains\SupportTechnical\Notifications\TicketStatusChangedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    protected TicketRepositoryInterface $repository;

    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all tickets with filters
     */
    public function getAllTickets(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById(int $ticketId): ?Ticket
    {
        return $this->repository->findById($ticketId);
    }

    /**
     * Create a new ticket with initial reply
     */
    public function createTicket(array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($data, $userId) {
            // Create the ticket
            $ticket = $this->repository->create([
                'user_id' => $userId,
                'title' => $data['title'],
                'type' => $data['type'] ?? null,
                'status' => TicketStatus::Pending,
                'priority' => $data['priority'],
            ]);

            // Create the initial reply with the content
            $this->repository->createReply($ticket->id, [
                'user_id' => $userId,
                'content' => $data['content'],
            ]);

            return $ticket->load(['user', 'replies']);
        });
    }

    /**
     * Update a ticket
     */
    public function updateTicket(int $ticketId, array $data, int $userId, bool $canUpdateAll = false): Ticket
    {
        $ticket = $this->repository->findById($ticketId);

        if (!$ticket) {
            throw new \Exception('Ticket no encontrado');
        }

        // Regular users can only update title if they are the owner
        if (!$canUpdateAll && $ticket->user_id !== $userId) {
            throw new \Exception('No tienes permiso para actualizar este ticket');
        }

        // Regular users can only update title
        if (!$canUpdateAll) {
            if (isset($data['status']) || isset($data['priority']) || isset($data['type'])) {
                throw new \Exception('No tienes permiso para actualizar estos campos');
            }
        }

        // Guardar estado anterior para notificación
        $oldStatus = $ticket->status->value;

        // Prepare update data
        $updateData = [];

        // Title can be updated by anyone with permission
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }

        // Only users with full update permission can update these fields
        if ($canUpdateAll) {
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (isset($data['priority'])) {
                $updateData['priority'] = $data['priority'];
            }
            if (isset($data['type'])) {
                $updateData['type'] = $data['type'];
            }
        }

        $updatedTicket = $this->repository->update($ticketId, $updateData);

        // Enviar notificación si el estado cambió
        if (isset($updateData['status']) && $oldStatus !== $updateData['status']->value) {
            $ticket->user->notify(new TicketStatusChangedNotification(
                $updatedTicket,
                $oldStatus,
                $updateData['status']->value
            ));
        }

        return $updatedTicket;
    }

    /**
     * Close a ticket
     */
    public function closeTicket(int $ticketId): Ticket
    {
        $ticket = $this->repository->findById($ticketId);

        if (!$ticket) {
            throw new \Exception('Ticket no encontrado');
        }

        // Check if already closed
        if ($ticket->status === TicketStatus::Closed) {
            throw new \Exception('El ticket ya está cerrado');
        }

        $oldStatus = $ticket->status->value;
        $closedTicket = $this->repository->close($ticketId);

        // Enviar notificación al dueño del ticket
        $ticket->user->notify(new TicketStatusChangedNotification(
            $closedTicket,
            $oldStatus,
            'closed'
        ));

        return $closedTicket;
    }

    /**
     * Reopen a ticket
     */
    public function reopenTicket(int $ticketId): Ticket
    {
        $ticket = $this->repository->findById($ticketId);

        if (!$ticket) {
            throw new \Exception('Ticket no encontrado');
        }

        // Check if already open
        if ($ticket->status !== TicketStatus::Closed) {
            throw new \Exception('El ticket no está cerrado');
        }

        $oldStatus = $ticket->status->value;
        $reopenedTicket = $this->repository->reopen($ticketId);

        // Enviar notificación al dueño del ticket
        $ticket->user->notify(new TicketStatusChangedNotification(
            $reopenedTicket,
            $oldStatus,
            'open'
        ));

        return $reopenedTicket;
    }

    /**
     * Get statistics
     */
    public function getStats(array $filters = []): array
    {
        return $this->repository->getStats($filters);
    }

    /**
     * Check if user can access ticket
     */
    public function canAccessTicket(Ticket $ticket, int $userId, bool $isSupport = false): bool
    {
        // Support can access all tickets
        if ($isSupport) {
            return true;
        }

        // Owner can access their own tickets
        return $ticket->user_id === $userId;
    }

    /**
     * Check if user is owner of ticket
     */
    public function isTicketOwner(Ticket $ticket, int $userId): bool
    {
        return $ticket->user_id === $userId;
    }
}
