<?php

namespace App\Domains\SupportTechnical\Http\Controllers;

use App\Domains\SupportTechnical\Services\TicketService;
use App\Domains\SupportTechnical\Http\Requests\AddTrackingCommentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TicketTrackingController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Add a comment to a ticket tracking.
     * 
     * @authenticated
     * POST /api/tickets/{ticket_id}/tracking
     */
    public function store(AddTrackingCommentRequest $request, int $ticketId): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // TODO: Obtener user_id del usuario autenticado cuando Auth esté implementado
            // $userId = auth()->id();
            // Por ahora usar user_id del request si existe, sino usar 1 por defecto
            $userId = $validated['user_id'] ?? $request->user()?->id ?? 1;
            
            $this->ticketService->addComment(
                $ticketId,
                $validated['comment'],
                $validated['action_type'],
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Comentario agregado exitosamente',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
