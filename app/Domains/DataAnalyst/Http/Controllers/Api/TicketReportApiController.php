<?php

namespace App\Domains\DataAnalyst\Http\Controllers\Api;

use App\Domains\DataAnalyst\Services\TicketReportService;
use App\Domains\DataAnalyst\Http\Requests\Api\TicketReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketReportApiController
{
    public function __construct(
        private TicketReportService $ticketReportService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/data-analyst/tickets",
     *     summary="Listado de tickets con filtros",
     *     tags={"DataAnalyst - Tickets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar por creación",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar por creación",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filtrar por prioridad",
     *         required=false,
     *         @OA\Schema(type="string", enum={"baja", "media", "alta", "critica"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"abierto", "en_proceso", "resuelto", "cerrado"})
     *     ),
     *     @OA\Parameter(
     *         name="technician_id",
     *         in="query",
     *         description="Filtrar por técnico asignado",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página actual",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de tickets obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="ticket_id", type="integer", example=1001),
     *                         @OA\Property(property="title", type="string", example="Error en el sistema de login"),
     *                         @OA\Property(property="description", type="string", example="Los usuarios no pueden iniciar sesión..."),
     *                         @OA\Property(property="priority", type="string", example="alta"),
     *                         @OA\Property(property="status", type="string", example="en_proceso"),
     *                         @OA\Property(property="category", type="string", example="Web"),
     *                         @OA\Property(property="creation_date", type="string", format="date-time"),
     *                         @OA\Property(property="assignment_date", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="resolution_date", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="close_date", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="assigned_technician", type="object", nullable=true,
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="user", type="object",
     *                                 @OA\Property(property="name", type="string", example="Pedro Sánchez")
     *                             )
     *                         ),
     *                         @OA\Property(property="escalations_count", type="integer", example=2)
     *                     )
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function index(TicketReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $tickets = $this->ticketReportService->getTicketReport($filters);

            // Transformar la respuesta para mostrar mejor el nombre del técnico
            $transformedTickets = $tickets->getCollection()->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_id' => $ticket->ticket_id,
                    'title' => $ticket->title,
                    'description' => $ticket->description,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'category' => $ticket->category,
                    'creation_date' => $ticket->creation_date,
                    'assignment_date' => $ticket->assignment_date,
                    'resolution_date' => $ticket->resolution_date,
                    'close_date' => $ticket->close_date,
                    'assigned_technician' => $ticket->assignedTechnician ? [
                        'id' => $ticket->assignedTechnician->id,
                        'name' => $ticket->assignedTechnician->user ?
                            $ticket->assignedTechnician->user->first_name . ' ' . $ticket->assignedTechnician->user->last_name :
                            'Técnico sin usuario'
                    ] : null,
                    'escalations_count' => $ticket->escalations->count()
                ];
            });

            $tickets->setCollection($transformedTickets);

            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            Log::error('API Error listing tickets', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el listado de tickets',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/tickets/stats/summary",
     *     summary="Obtener estadísticas completas de tickets",
     *     tags={"DataAnalyst - Tickets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar estadísticas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar estadísticas",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría específica",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filtrar por prioridad específica",
     *         required=false,
     *         @OA\Schema(type="string", enum={"baja", "media", "alta", "critica"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_tickets", type="integer", example=156),
     *                 @OA\Property(property="by_status", type="object",
     *                     @OA\Property(property="abierto", type="integer", example=23),
     *                     @OA\Property(property="en_proceso", type="integer", example=45),
     *                     @OA\Property(property="resuelto", type="integer", example=67),
     *                     @OA\Property(property="cerrado", type="integer", example=21)
     *                 ),
     *                 @OA\Property(property="by_priority", type="object",
     *                     @OA\Property(property="baja", type="integer", example=34),
     *                     @OA\Property(property="media", type="integer", example=78),
     *                     @OA\Property(property="alta", type="integer", example=32),
     *                     @OA\Property(property="critica", type="integer", example=12)
     *                 ),
     *                 @OA\Property(property="by_category", type="object",
     *                     @OA\Property(property="Web", type="integer", example=45),
     *                     @OA\Property(property="Infraestructura", type="integer", example=34),
     *                     @OA\Property(property="Seguridad", type="integer", example=23),
     *                     @OA\Property(property="LMS", type="integer", example=54)
     *                 ),
     *                 @OA\Property(property="resolution_metrics", type="object",
     *                     @OA\Property(property="average_resolution_time_hours", type="number", format="float", example=8.5),
     *                     @OA\Property(property="median_resolution_time_hours", type="number", format="float", example=6.0),
     *                     @OA\Property(property="first_response_time_hours", type="number", format="float", example=2.3)
     *                 ),
     *                 @OA\Property(property="technician_performance", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="technician_id", type="integer", example=12),
     *                         @OA\Property(property="technician_name", type="string", example="Pedro Sánchez"),
     *                         @OA\Property(property="tickets_resolved", type="integer", example=45),
     *                         @OA\Property(property="average_resolution_time_hours", type="number", format="float", example=7.2)
     *                     )
     *                 ),
     *                 @OA\Property(property="escalation_rate", type="number", format="float", example=15.3)
     *             )
     *         )
     *     )
     * )
     */
    public function getStatistics(TicketReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $statistics = $this->ticketReportService->getTicketStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting ticket statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de tickets',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/tickets/stats/categories",
     *     summary="Obtener estadísticas detalladas por categoría",
     *     tags={"DataAnalyst - Tickets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas por categoría obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="category", type="string", example="Web"),
     *                     @OA\Property(property="total_tickets", type="integer", example=45),
     *                     @OA\Property(property="open_tickets", type="integer", example=12),
     *                     @OA\Property(property="resolved_tickets", type="integer", example=28),
     *                     @OA\Property(property="average_resolution_time", type="number", format="float", example=6.5),
     *                     @OA\Property(property="escalation_count", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCategoryStats(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date']);
            $categoryStats = $this->ticketReportService->getCategoryStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $categoryStats
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting category statistics', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas por categoría',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/data-analyst/tickets/stats/technicians",
     *     summary="Obtener ranking de técnicos por rendimiento",
     *     tags={"DataAnalyst - Tickets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Fecha de inicio para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Fecha de fin para filtrar",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Límite de técnicos a mostrar",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=50, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ranking de técnicos obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="rank", type="integer", example=1),
     *                     @OA\Property(property="technician_id", type="integer", example=12),
     *                     @OA\Property(property="technician_name", type="string", example="Pedro Sánchez"),
     *                     @OA\Property(property="total_tickets", type="integer", example=67),
     *                     @OA\Property(property="resolved_tickets", type="integer", example=45),
     *                     @OA\Property(property="resolution_rate", type="number", format="float", example=67.2),
     *                     @OA\Property(property="average_resolution_time", type="number", format="float", example=7.2),
     *                     @OA\Property(property="escalation_count", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTechnicianRanking(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'limit']);
            $ranking = $this->ticketReportService->getTechnicianRanking($filters);

            return response()->json([
                'success' => true,
                'data' => $ranking
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting technician ranking', [
                'filters' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el ranking de técnicos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
