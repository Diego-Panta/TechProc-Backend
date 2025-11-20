<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentTypes\AlertService;
use App\Domains\DeveloperWeb\Http\Requests\ContentTypes\StoreAlertRequest;
use App\Domains\DeveloperWeb\Http\Requests\ContentTypes\UpdateAlertRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlertApiController
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'item_type', 'search']);
            $perPage = $request->get('per_page', 15);

            $alerts = $this->alertService->getAll($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing alerts', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas'
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $alert = $this->alertService->getById($id);

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
            Log::error('Error showing alert', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la alerta'
            ], 500);
        }
    }

    public function store(StoreAlertRequest $request): JsonResponse
    {
        try {
            $alert = $this->alertService->create($request->validated());

            Log::info('Alerta creada', ['id' => $alert->id, 'title' => $alert->title]);

            return response()->json([
                'success' => true,
                'data' => $alert,
                'message' => 'Alerta creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating alert', ['data' => $request->all(), 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la alerta'
            ], 500);
        }
    }

    public function update(UpdateAlertRequest $request, int $id): JsonResponse // CAMBIADO
    {
        try {
            $success = $this->alertService->update($id, $request->validated());

            if ($success) {
                Log::info('Alerta actualizada', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Alerta actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Alerta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error updating alert', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la alerta'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->alertService->delete($id);

            if ($success) {
                Log::info('Alerta eliminada', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Alerta eliminada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Alerta no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting alert', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la alerta'
            ], 500);
        }
    }

    // MÉTODOS ESPECÍFICOS SOLICITADOS
    
    public function getPublished(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $alerts = $this->alertService->getPublishedAlerts($perPage);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting published alerts', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener alertas publicadas'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de alertas
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->alertService->getStats();

            Log::info('Estadísticas de alertas consultadas');

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de alertas', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de alertas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}