<?php

namespace App\Domains\SupportTechnical\Http\Controllers;

use App\Domains\SupportTechnical\Services\TicketService;
use App\Domains\SupportTechnical\Services\TicketReplyService;
use App\Domains\SupportTechnical\Http\Requests\CreateReplyRequest;
use App\Domains\SupportTechnical\Http\Requests\UpdateReplyRequest;
use App\Domains\SupportTechnical\Resources\TicketReplyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketReplyController extends Controller
{
    protected TicketService $ticketService;
    protected TicketReplyService $replyService;

    public function __construct(
        TicketService $ticketService,
        TicketReplyService $replyService
    ) {
        $this->ticketService = $ticketService;
        $this->replyService = $replyService;
    }

    /**
     * Create a new reply
     * POST /api/support/tickets/{ticketId}/replies
     */
    public function store(CreateReplyRequest $request, int $ticketId): JsonResponse
    {
        try {
            $user = $request->user();
            $ticket = $this->ticketService->getTicketById($ticketId);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket no encontrado'
                ], 404);
            }

            // Authorize to view the ticket (need access to reply)
            $this->authorize('view', $ticket);

            // Verificar si es soporte/admin
            $isSupport = $user->hasRole(['support', 'admin']);

            $data = $request->validated();
            $attachments = $request->hasFile('attachments') ? $request->file('attachments') : null;

            $reply = $this->replyService->createReply(
                $ticketId,
                $data,
                $user->id,
                $attachments,
                $isSupport
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Respuesta agregada exitosamente',
                'data' => ['reply' => new TicketReplyResource($reply)]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update a reply
     * PUT /api/support/tickets/{ticketId}/replies/{replyId}
     */
    public function update(UpdateReplyRequest $request, int $ticketId, int $replyId): JsonResponse
    {
        try {
            $user = $request->user();
            $reply = $this->replyService->getReplyById($replyId);

            if (!$reply) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Respuesta no encontrada'
                ], 404);
            }

            // Authorize - si falla, lanza AuthorizationException (403)
            $this->authorize('update', $reply);

            // Verificar si es soporte/admin (puede editar sin límite de tiempo)
            $isSupport = $user->hasPermissionTo('ticket-replies.update') && 
                        $user->hasRole(['support', 'admin']);

            $data = $request->validated();
            $updatedReply = $this->replyService->updateReply(
                $replyId, 
                $data, 
                $user->id,
                $isSupport
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Respuesta actualizada exitosamente',
                'data' => ['reply' => new TicketReplyResource($updatedReply)]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // Error de autorización (403)
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para editar esta respuesta'
            ], 403);
        } catch (\Exception $e) {
            // Errores de validación de negocio (400)
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a reply
     * DELETE /api/support/tickets/{ticketId}/replies/{replyId}
     */
    public function destroy(Request $request, int $ticketId, int $replyId): JsonResponse
    {
        try {
            $user = $request->user();
            $reply = $this->replyService->getReplyById($replyId);

            if (!$reply) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Respuesta no encontrada'
                ], 404);
            }

            // Authorize - la policy verifica permisos
            $this->authorize('delete', $reply);

            $this->replyService->deleteReply($replyId);

            return response()->json([
                'status' => 'success',
                'message' => 'Respuesta eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
