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
     * Listar alertas públicas activas (SIN AUTENTICACIÓN)
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
     * Listar alertas públicas de alta prioridad (SIN AUTENTICACIÓN)
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
     * Obtener detalles de alerta pública (SIN AUTENTICACIÓN)
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
            if (
                $alert->status !== 'active' ||
                $alert->start_date > $now ||
                $alert->end_date < $now
            ) {
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
     * Listar alertas (PROTEGIDO - CON AUTENTICACIÓN)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado desde el middleware
            $user = $request->user();

            $filters = [
                'status' => $request->get('status'),
                'type' => $request->get('type'),
                'priority' => $request->get('priority'),
            ];

            $perPage = $request->get('per_page', 15);

            $alerts = $this->alertService->getAllAlerts($perPage, $filters);

            // Log de acceso
            Log::info('Usuario accedió a listado de alertas', [
                'user_id' => $user->id,
                'email' => $user->email,
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            Log::error('API Error listing alerts', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Obtener alerta específica (PROTEGIDO - CON AUTENTICACIÓN)
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $alert = $this->alertService->getAlertById($id);

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alerta no encontrada'
                ], 404);
            }

            Log::info('Usuario accedió a alerta específica', [
                'user_id' => $user->id,
                'alert_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'data' => $alert
            ]);
        } catch (\Exception $e) {
            Log::error('API Error showing alert', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Crear nueva alerta (PROTEGIDO - CON AUTENTICACIÓN)
     */
    public function store(StoreAlertApiRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Pasar el ID del usuario autenticado al servicio
            $alert = $this->alertService->createAlert(
                $request->validated(),
                $user->id
            );

            // Log de creación
            Log::info('Usuario creó nueva alerta', [
                'user_id' => $user->id,
                'alert_id' => $alert->id,
                'message' => $alert->message
            ]);

            return response()->json([
                'success' => true,
                'data' => $alert,
                'message' => 'Alerta creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('API Error creating alert', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Actualizar alerta (PROTEGIDO - CON AUTENTICACIÓN)
     */
    public function update(UpdateAlertApiRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $alert = $this->alertService->getAlertById($id);

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alerta no encontrada'
                ], 404);
            }

            $success = $this->alertService->updateAlert($id, $request->validated());

            if ($success) {
                // Recargar la alerta actualizada
                $updatedAlert = $this->alertService->getAlertById($id);

                Log::info('Usuario actualizó alerta', [
                    'user_id' => $user->id,
                    'alert_id' => $id
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $updatedAlert,
                    'message' => 'Alerta actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la alerta'
            ], 400);
        } catch (\Exception $e) {
            Log::error('API Error updating alert', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la alerta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Eliminar alerta (PROTEGIDO - CON AUTENTICACIÓN)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            $success = $this->alertService->deleteAlert($id);

            if ($success) {
                Log::info('Usuario eliminó alerta', [
                    'user_id' => $user->id,
                    'alert_id' => $id
                ]);

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
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Obtener estadísticas (PROTEGIDO - CON AUTENTICACIÓN)
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $statusCounts = $this->alertService->getStatusCounts();
            $typeCounts = $this->alertService->getTypeCounts();
            $activeAlerts = $this->alertService->getActiveAlerts();
            $highPriorityAlerts = $this->alertService->getHighPriorityAlerts();

            $total = array_sum($statusCounts);

            Log::info('Usuario accedió a estadísticas de alertas', [
                'user_id' => $user->id
            ]);

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
                'user_id' => $request->user()->id ?? 'unknown',
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
