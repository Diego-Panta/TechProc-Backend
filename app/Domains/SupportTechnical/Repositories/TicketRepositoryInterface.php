<?php

namespace App\Domains\SupportTechnical\Repositories;

use IncadevUns\CoreDomain\Models\Ticket;
use IncadevUns\CoreDomain\Models\TicketReply;
use IncadevUns\CoreDomain\Models\ReplyAttachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TicketRepositoryInterface
{
    /**
     * Get all tickets with filters and pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a ticket by ID
     */
    public function findById(int $ticketId): ?Ticket;

    /**
     * Create a new ticket
     */
    public function create(array $data): Ticket;

    /**
     * Update an existing ticket
     */
    public function update(int $ticketId, array $data): Ticket;

    /**
     * Close a ticket
     */
    public function close(int $ticketId): Ticket;

    /**
     * Reopen a ticket
     */
    public function reopen(int $ticketId): Ticket;

    /**
     * Create a reply for a ticket
     */
    public function createReply(int $ticketId, array $data): TicketReply;

    /**
     * Update a reply
     */
    public function updateReply(int $replyId, array $data): TicketReply;

    /**
     * Delete a reply
     */
    public function deleteReply(int $replyId): bool;

    /**
     * Find a reply by ID
     */
    public function findReplyById(int $replyId): ?TicketReply;

    /**
     * Create an attachment for a reply
     */
    public function createAttachment(int $replyId, array $data): ReplyAttachment;

    /**
     * Delete an attachment
     */
    public function deleteAttachment(int $attachmentId): bool;

    /**
     * Find an attachment by ID
     */
    public function findAttachmentById(int $attachmentId): ?ReplyAttachment;

    /**
     * Get statistics
     */
    public function getStats(array $filters = []): array;
}
