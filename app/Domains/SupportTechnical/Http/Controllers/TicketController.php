<?php

namespace App\Domains\SupportTechnical\Http\Controllers;

use App\Domains\SupportTechnical\Services\TicketService;
use App\Domains\SupportTechnical\Http\Requests\CreateTicketRequest;
use App\Domains\SupportTechnical\Http\Requests\TakeTicketRequest;
use App\Domains\SupportTechnical\Http\Requests\UpdateTicketStatusRequest;
use App\Domains\SupportTechnical\Http\Requests\ResolveTicketRequest;
use App\Domains\SupportTechnical\Http\Requests\CloseTicketRequest;
use App\Domains\SupportTechnical\Resources\TicketCollection;
use App\Domains\SupportTechnical\Resources\TicketDetailResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);
        
        $filters = [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'category' => $request->input('category'),
            'user_id' => $request->input('user_id'),
            'technician_id' => $request->input('technician_id'),
            'search' => $request->input('search'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $tickets = $this->ticketService->getAllTickets($filters, $perPage);

        return response()->json(['success' => true, 'data' => new TicketCollection($tickets)]);
    }

    public function show(int $ticketId): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($ticketId);

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => new TicketDetailResource($ticket)]);
    }

    public function store(CreateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->createTicket($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado exitosamente',
            'data' => ['id' => $ticket->id, 'ticket_id' => $ticket->ticket_id],
        ], 201);
    }

    public function take(TakeTicketRequest $request, int $ticketId): JsonResponse
    {
        try {
            $ticket = $this->ticketService->takeTicket($ticketId, $request->validated()['technician_id']);

            return response()->json([
                'success' => true,
                'message' => 'Ticket asignado exitosamente',
                'data' => ['ticket_id' => $ticket->ticket_id, 'status' => $ticket->status],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al asignar ticket', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(UpdateTicketStatusRequest $request, int $ticketId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $ticket = $this->ticketService->updateTicketStatus($ticketId, $validated['status'], $validated['notes'] ?? null);

            return response()->json(['success' => true, 'message' => 'Estado actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar estado', 'error' => $e->getMessage()], 500);
        }
    }

    public function resolve(ResolveTicketRequest $request, int $ticketId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $ticket = $this->ticketService->resolveTicket($ticketId, $validated['technician_id'], $validated['resolution_notes']);

            return response()->json(['success' => true, 'message' => 'Ticket resuelto exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al resolver ticket', 'error' => $e->getMessage()], 500);
        }
    }

    public function close(CloseTicketRequest $request, int $ticketId): JsonResponse
    {
        try {
            $ticket = $this->ticketService->closeTicket($ticketId, $request->validated()['closing_notes']);

            return response()->json(['success' => true, 'message' => 'Ticket cerrado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cerrar ticket', 'error' => $e->getMessage()], 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $stats = $this->ticketService->getStats($filters);

        return response()->json([
            'success' => true,
            'data' => [
                'total_tickets' => $stats['total_tickets'],
                'by_status' => [
                    'abierto' => $stats['by_status']['abierto'] ?? 0,
                    'en_proceso' => $stats['by_status']['en_proceso'] ?? 0,
                    'resuelto' => $stats['by_status']['resuelto'] ?? 0,
                    'cerrado' => $stats['by_status']['cerrado'] ?? 0,
                ],
                'by_priority' => [
                    'baja' => $stats['by_priority']['baja'] ?? 0,
                    'media' => $stats['by_priority']['media'] ?? 0,
                    'alta' => $stats['by_priority']['alta'] ?? 0,
                    'critica' => $stats['by_priority']['critica'] ?? 0,
                ],
                'by_category' => $stats['by_category'],
                'average_resolution_time_hours' => $stats['average_resolution_time_hours'],
                'pending_escalations' => $stats['pending_escalations'],
            ],
        ]);
    }
}
