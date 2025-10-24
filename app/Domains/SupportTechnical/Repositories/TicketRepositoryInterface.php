<?php

namespace App\Domains\SupportTechnical\Repositories;

use App\Domains\SupportTechnical\Models\Ticket;
use App\Domains\SupportTechnical\Models\TicketTracking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TicketRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findById(int $ticketId): ?Ticket;
    public function create(array $data): Ticket;
    public function update(int $ticketId, array $data): Ticket;
    public function delete(int $ticketId): bool;
    public function createTracking(int $ticketId, array $data): TicketTracking;
    public function getStats(array $filters = []): array;
}
