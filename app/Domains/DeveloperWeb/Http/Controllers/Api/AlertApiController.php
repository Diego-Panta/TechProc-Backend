<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Http\Requests\Api\StoreAlertApiRequest;
use App\Domains\DeveloperWeb\Http\Requests\Api\UpdateAlertApiRequest;
use App\Domains\DeveloperWeb\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlertApiController
{
    public function __construct(
        private AlertService $alertService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/developer-web/alerts",
     *     summary="Listar alertas (admin)",
     *     tags={"Alerts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"info", "warning", "error", "success", "maintenance"})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filtrar por prioridad",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=5)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página para paginación",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de alertas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Alert")
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'type' => $request->get('type'),
                'priority' => $request->get('priority'),
            ];

            $perPage = $request->get('per_page', 15);

            $alerts = $this->alertService->getAllAlerts($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing alerts', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/alerts/public",
     *     summary="Listar alertas públicas activas",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"info", "warning", "error", "success", "maintenance"})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filtrar por prioridad",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de alertas públicas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Alert")
     *             )
     *         )
     *     )
     * )
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $filters = [
                'type' => $request->get('type'),
                'priority' => $request->get('priority'),
            ];

            $alerts = $this->alertService->getAlertsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing public alerts', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas públicas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/alerts/public/high-priority",
     *     summary="Listar alertas públicas de alta prioridad",
     *     tags={"Alerts"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de alertas de alta prioridad",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Alert")
     *             )
     *         )
     *     )
     * )
     */
    public function publicHighPriority(): JsonResponse
    {
        try {
            $alerts = $this->alertService->getHighPriorityAlerts();

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing high priority alerts', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas de alta prioridad',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/alerts/{id}",
     *     summary="Obtener detalles de una alerta",
     *     tags={"Alerts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la alerta",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Alert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $alert = $this->alertService->getAlertById($id);

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alerta no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $alert
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing alert', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la alerta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/alerts/public/{id}",
     *     summary="Obtener detalles de una alerta pública",
     *     tags={"Alerts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la alerta pública",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Alert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     )
     * )
     */
    public function publicShow(int $id): JsonResponse
    {
        try {
            $alert = $this->alertService->getAlertById($id);

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alerta no encontrada'
                ], 404);
            }

            // Verificar que la alerta esté activa
            $now = now();
            if ($alert->status !== 'active' || 
                $alert->start_date > $now || 
                $alert->end_date < $now) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alerta no disponible'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $alert
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing public alert', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la alerta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/alerts",
     *     summary="Crear una nueva alerta",
     *     tags={"Alerts"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message", "type", "status", "start_date", "end_date", "priority"},
     *             @OA\Property(property="message", type="string", minLength=5, maxLength=1000),
     *             @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success", "maintenance"}),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}),
     *             @OA\Property(property="link_url", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="link_text", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="end_date", type="string", format="date-time"),
     *             @OA\Property(property="priority", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="created_by", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Alerta creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Alert"),
     *             @OA\Property(property="message", type="string", example="Alerta creada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(StoreAlertApiRequest $request): JsonResponse
    {
        try {
            $alert = $this->alertService->createAlert($request->validated());

            return response()->json([
                'success' => true,
                'data' => $alert,
                'message' => 'Alerta creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating alert', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la alerta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/developer-web/alerts/{id}",
     *     summary="Actualizar una alerta",
     *     tags={"Alerts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", minLength=5, maxLength=1000),
     *             @OA\Property(property="type", type="string", enum={"info", "warning", "error", "success", "maintenance"}),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}),
     *             @OA\Property(property="link_url", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="link_text", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="end_date", type="string", format="date-time"),
     *             @OA\Property(property="priority", type="integer", minimum=1, maximum=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alerta actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alerta actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     )
     * )
     */
    public function update(UpdateAlertApiRequest $request, int $id): JsonResponse
    {
        try {
            $success = $this->alertService->updateAlert($id, $request->validated());

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alerta actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la alerta'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error updating alert', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la alerta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/developer-web/alerts/{id}",
     *     summary="Eliminar una alerta",
     *     tags={"Alerts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la alerta",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alerta eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Alerta eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alerta no encontrada"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->alertService->deleteAlert($id);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alerta eliminada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la alerta'
            ], 404);

        } catch (\Exception $e) {
            Log::error('API Error deleting alert', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la alerta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/alerts/stats/summary",
     *     summary="Obtener estadísticas de alertas",
     *     tags={"Alerts"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="active", type="integer", example=25),
     *                 @OA\Property(property="inactive", type="integer", example=25),
     *                 @OA\Property(property="info", type="integer", example=15),
     *                 @OA\Property(property="warning", type="integer", example=10),
     *                 @OA\Property(property="error", type="integer", example=5),
     *                 @OA\Property(property="success", type="integer", example=10),
     *                 @OA\Property(property="maintenance", type="integer", example=10),
     *                 @OA\Property(property="high_priority", type="integer", example=8),
     *                 @OA\Property(property="active_count", type="integer", example=12)
     *             )
     *         )
     *     )
     * )
     */
    public function getStats(): JsonResponse
    {
        try {
            $statusCounts = $this->alertService->getStatusCounts();
            $typeCounts = $this->alertService->getTypeCounts();
            $priorityCounts = $this->alertService->getPriorityCounts();
            $activeAlerts = $this->alertService->getActiveAlerts();
            $highPriorityAlerts = $this->alertService->getHighPriorityAlerts();
            
            $total = array_sum($statusCounts);

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $statusCounts['active'] ?? 0,
                    'inactive' => $statusCounts['inactive'] ?? 0,
                    'info' => $typeCounts['info'] ?? 0,
                    'warning' => $typeCounts['warning'] ?? 0,
                    'error' => $typeCounts['error'] ?? 0,
                    'success' => $typeCounts['success'] ?? 0,
                    'maintenance' => $typeCounts['maintenance'] ?? 0,
                    'high_priority' => $highPriorityAlerts->count(),
                    'active_count' => $activeAlerts->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting alert stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}