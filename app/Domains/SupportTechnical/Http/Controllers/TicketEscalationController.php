<?php

namespace App\Domains\SupportTechnical\Http\Controllers;

use App\Domains\SupportTechnical\Services\EscalationService;
use App\Domains\SupportTechnical\Http\Requests\EscalateTicketRequest;
use App\Domains\SupportTechnical\Resources\EscalationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketEscalationController extends Controller
{
    protected EscalationService $escalationService;

    public function __construct(EscalationService $escalationService)
    {
        $this->escalationService = $escalationService;
    }

    /**
     * Escalate a ticket.
     * 
     * @authenticated
     * POST /api/tickets/{ticket_id}/escalate
     */
    public function escalate(EscalateTicketRequest $request, int $ticketId): JsonResponse
    {
        try {
            $escalation = $this->escalationService->escalateTicket($ticketId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Escalación creada exitosamente. Pendiente de aprobación.',
                'data' => [
                    'escalation_id' => $escalation->escalation_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * List escalations.
     * 
     * @authenticated
     * GET /api/tickets/escalations
     * 
     * Query Parameters:
     * - ticket_id (int): Filtrar por ticket
     * - approved (boolean): Filtrar por estado de aprobación
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'ticket_id' => $request->input('ticket_id'),
            'approved' => $request->has('approved') ? filter_var($request->input('approved'), FILTER_VALIDATE_BOOLEAN) : null,
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $escalations = $this->escalationService->getAllEscalations($filters);

        return response()->json([
            'success' => true,
            'data' => EscalationResource::collection($escalations),
        ]);
    }

    /**
     * Approve an escalation.
     * 
     * @authenticated
     * POST /api/tickets/escalations/{escalation_id}/approve
     */
    public function approve(int $escalationId): JsonResponse
    {
        try {
            $this->escalationService->approveEscalation($escalationId);

            return response()->json([
                'success' => true,
                'message' => 'Escalación aprobada. Ticket reasignado.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
