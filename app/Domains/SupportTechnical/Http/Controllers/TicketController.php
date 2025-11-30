<?php

namespace App\Domains\SupportTechnical\Http\Controllers;

use App\Domains\SupportTechnical\Services\TicketService;
use App\Domains\SupportTechnical\Http\Requests\ListTicketsRequest;
use App\Domains\SupportTechnical\Http\Requests\CreateTicketRequest;
use App\Domains\SupportTechnical\Http\Requests\UpdateTicketRequest;
use App\Domains\SupportTechnical\Resources\TicketCollection;
use App\Domains\SupportTechnical\Resources\TicketResource;
use App\Http\Controllers\Controller;
use IncadevUns\CoreDomain\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * List tickets with filters
     * GET /api/support/tickets
     */
    public function index(ListTicketsRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Authorize - verifica que el usuario pueda ver tickets
            $this->authorize('viewAny', Ticket::class);

            // Verificar si puede ver TODOS los tickets o solo los propios
            $canViewAll = $user->can('viewAll', Ticket::class);

            $perPage = $request->input('per_page', 15);

            $filters = [
                'status' => $request->input('status'),
                'priority' => $request->input('priority'),
                'type' => $request->input('type'),
                'search' => $request->input('search'),
                'sort_by' => $request->input('sort_by', 'updated_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            // Si no puede ver todos, filtrar solo sus propios tickets
            if (!$canViewAll) {
                $filters['user_id'] = $user->id;
            }

            $filters = array_filter($filters, fn($value) => !is_null($value));
            $tickets = $this->ticketService->getAllTickets($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => new TicketCollection($tickets)
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para ver tickets'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show ticket detail
     * GET /api/support/tickets/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $ticket = $this->ticketService->getTicketById($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket no encontrado'
                ], 404);
            }

            // DEBUG: Log para verificar autorización
            Log::info('Ticket Authorization Debug', [
                'ticket_id' => $ticket->id,
                'ticket_user_id' => $ticket->user_id,
                'ticket_user_id_type' => gettype($ticket->user_id),
                'auth_user_id' => $user->id,
                'auth_user_id_type' => gettype($user->id),
                'auth_user_email' => $user->email,
                'auth_user_roles' => $user->getRoleNames()->toArray(),
                'comparison_strict' => $ticket->user_id === $user->id,
                'comparison_loose' => $ticket->user_id == $user->id,
                'has_permission_view_any' => $user->hasPermissionTo('tickets.view-any'),
                'has_role_support_admin' => $user->hasRole(['support', 'admin']),
            ]);

            // Authorize
            $this->authorize('view', $ticket);

            return response()->json([
                'status' => 'success',
                'data' => ['ticket' => new TicketResource($ticket)]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para ver este ticket'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new ticket
     * POST /api/support/tickets
     */
    public function store(CreateTicketRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Authorize
            $this->authorize('create', Ticket::class);

            $ticket = $this->ticketService->createTicket(
                $request->validated(),
                $user->id
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket creado exitosamente',
                'data' => ['ticket' => new TicketResource($ticket)]
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para crear tickets'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a ticket
     * PUT /api/support/tickets/{id}
     */
    public function update(UpdateTicketRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $ticket = $this->ticketService->getTicketById($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket no encontrado'
                ], 404);
            }

            // Authorize
            $this->authorize('update', $ticket);

            // Verificar si puede actualizar todos los campos (status, priority, type)
            $canUpdateAll = $user->hasPermissionTo('tickets.update') ||
                           $user->hasRole(['support', 'admin']);

            $updatedTicket = $this->ticketService->updateTicket(
                $id,
                $request->validated(),
                $user->id,
                $canUpdateAll
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket actualizado exitosamente',
                'data' => ['ticket' => new TicketResource($updatedTicket)]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para actualizar este ticket'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Close a ticket
     * POST /api/support/tickets/{id}/close
     */
    public function close(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $ticket = $this->ticketService->getTicketById($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket no encontrado'
                ], 404);
            }

            // Authorize - la policy verifica si puede cerrar el ticket
            $this->authorize('close', $ticket);

            $closedTicket = $this->ticketService->closeTicket($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket cerrado exitosamente',
                'data' => [
                    'ticket' => [
                        'id' => $closedTicket->id,
                        'status' => $closedTicket->status->value,
                        'updated_at' => $closedTicket->updated_at->toISOString()
                    ]
                ]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para cerrar este ticket'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reopen a ticket
     * POST /api/support/tickets/{id}/reopen
     */
    public function reopen(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $ticket = $this->ticketService->getTicketById($id);

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket no encontrado'
                ], 404);
            }

            // Authorize - la policy verifica si puede reabrir el ticket
            $this->authorize('reopen', $ticket);

            $reopenedTicket = $this->ticketService->reopenTicket($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket reabierto exitosamente',
                'data' => [
                    'ticket' => [
                        'id' => $reopenedTicket->id,
                        'status' => $reopenedTicket->status->value,
                        'updated_at' => $reopenedTicket->updated_at->toISOString()
                    ]
                ]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para reabrir este ticket'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get my tickets (tickets created by the authenticated user)
     * GET /api/support/my-tickets
     */
    public function myTickets(ListTicketsRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $perPage = $request->input('per_page', 15);

            $filters = [
                'user_id' => $user->id, // Siempre filtrar por el usuario actual
                'status' => $request->input('status'),
                'priority' => $request->input('priority'),
                'type' => $request->input('type'),
                'search' => $request->input('search'),
                'sort_by' => $request->input('sort_by', 'updated_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $filters = array_filter($filters, fn($value) => !is_null($value));
            $tickets = $this->ticketService->getAllTickets($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => new TicketCollection($tickets)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics
     * GET /api/support/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Authorize
            $this->authorize('viewStatistics', Ticket::class);

            $filters = [
                'period' => $request->input('period', 'month')
            ];

            $stats = $this->ticketService->getStats($filters);

            return response()->json([
                'status' => 'success',
                'data' => ['statistics' => $stats]
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permiso para ver las estadísticas'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user is support
     */
    private function isSupport($user): bool
    {
        if (!$user || !isset($user->role)) {
            return false;
        }

        $roles = is_array($user->role) ? $user->role : json_decode($user->role, true);
        return in_array('support', $roles) || in_array('admin', $roles);
    }
}
