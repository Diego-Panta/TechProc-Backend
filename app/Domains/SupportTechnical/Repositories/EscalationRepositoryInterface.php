<?php

namespace App\Domains\SupportTechnical\Repositories;

use App\Domains\SupportTechnical\Models\Escalation;
use Illuminate\Database\Eloquent\Collection;

interface EscalationRepositoryInterface
{
    public function getAll(array $filters = []): Collection;
    public function findById(int $escalationId): ?Escalation;
    public function create(array $data): Escalation;
    public function approve(int $escalationId): Escalation;
}
