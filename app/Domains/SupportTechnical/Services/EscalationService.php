<?php

namespace App\Domains\SupportTechnical\Services;

use App\Domains\SupportTechnical\Models\Escalation;
use App\Domains\SupportTechnical\Repositories\EscalationRepositoryInterface;
use App\Domains\SupportTechnical\Repositories\TicketRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EscalationService
{
    protected EscalationRepositoryInterface $escalationRepository;
    protected TicketRepositoryInterface $ticketRepository;

    public function __construct(
        EscalationRepositoryInterface $escalationRepository,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->escalationRepository = $escalationRepository;
        $this->ticketRepository = $ticketRepository;
    }

    public function getAllEscalations(array $filters = []): Collection
    {
        return $this->escalationRepository->getAll($filters);
    }

    public function escalateTicket(int $ticketId, array $data): Escalation
    {
        return DB::transaction(function () use ($ticketId, $data) {
            // Crear la escalación
            $data['ticket_id'] = $ticketId;
            $data['escalation_date'] = now();
            $data['approved'] = false;

            $escalation = $this->escalationRepository->create($data);

            // Asignar escalation_id si no existe
            if (!$escalation->escalation_id) {
                $escalation->escalation_id = $escalation->id;
                $escalation->save();
            }

            // Crear registro de seguimiento
            $this->ticketRepository->createTracking($ticketId, [
                'action_type' => 'escalacion',
                'comment' => "Ticket escalado. Razón: {$data['escalation_reason']}",
                'user_id' => $data['technician_origin_id'],
            ]);

            return $escalation->fresh(['ticket', 'technicianOrigin', 'technicianDestiny']);
        });
    }

    public function approveEscalation(int $escalationId): Escalation
    {
        return DB::transaction(function () use ($escalationId) {
            $escalation = $this->escalationRepository->approve($escalationId);

            // Actualizar el ticket con el nuevo técnico
            $this->ticketRepository->update($escalation->ticket_id, [
                'assigned_technician' => $escalation->technician_destiny_id,
            ]);

            // Crear registro de seguimiento
            $this->ticketRepository->createTracking($escalation->ticket_id, [
                'action_type' => 'escalacion_aprobada',
                'comment' => 'Escalación aprobada. Ticket reasignado.',
                'user_id' => $escalation->technician_destiny_id,
            ]);

            return $escalation;
        });
    }
}
