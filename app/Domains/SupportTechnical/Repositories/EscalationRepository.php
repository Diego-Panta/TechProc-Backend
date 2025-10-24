<?php

namespace App\Domains\SupportTechnical\Repositories;

use App\Domains\SupportTechnical\Models\Escalation;
use Illuminate\Database\Eloquent\Collection;

class EscalationRepository implements EscalationRepositoryInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = Escalation::with(['ticket', 'technicianOrigin', 'technicianDestiny']);

        if (isset($filters['approved'])) {
            $query->where('approved', $filters['approved']);
        }

        if (isset($filters['ticket_id'])) {
            $query->where('ticket_id', $filters['ticket_id']);
        }

        return $query->orderBy('escalation_date', 'desc')->get();
    }

    public function findById(int $escalationId): ?Escalation
    {
        return Escalation::with(['ticket', 'technicianOrigin', 'technicianDestiny'])
            ->where('escalation_id', $escalationId)
            ->orWhere('id', $escalationId)
            ->first();
    }

    public function create(array $data): Escalation
    {
        return Escalation::create($data);
    }

    public function approve(int $escalationId): Escalation
    {
        $escalation = Escalation::where('escalation_id', $escalationId)
            ->orWhere('id', $escalationId)
            ->firstOrFail();
        
        $escalation->update([
            'approved' => true,
            'approval_date' => now(),
        ]);
        
        return $escalation->fresh(['ticket', 'technicianOrigin', 'technicianDestiny']);
    }
}
