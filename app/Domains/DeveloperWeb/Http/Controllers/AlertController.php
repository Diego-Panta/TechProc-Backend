<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Http\Requests\StoreAlertRequest;
use App\Domains\DeveloperWeb\Http\Requests\UpdateAlertRequest;
use App\Domains\DeveloperWeb\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AlertController
{
    public function __construct(
        private AlertService $alertService
    ) {}

    // Listar alertas (panel admin)
    public function index(): View
    {
        $filters = [
            'status' => request('status'),
            'type' => request('type'),
            'priority' => request('priority'),
        ];

        $alerts = $this->alertService->getAllAlerts(15, $filters);
        $statusCounts = $this->alertService->getStatusCounts();
        $typeCounts = $this->alertService->getTypeCounts();
        $priorityCounts = $this->alertService->getPriorityCounts();

        return view('developer-web.alerts.index', compact(
            'alerts',
            'statusCounts',
            'typeCounts',
            'priorityCounts',
            'filters'
        ));
    }

    // Mostrar formulario de creación
    public function create(): View
    {
        return view('developer-web.alerts.create');
    }

    // Almacenar nueva alerta
    public function store(StoreAlertRequest $request): RedirectResponse
    {
        try {
            $alert = $this->alertService->createAlert($request->validated());

            return redirect()->route('developer-web.alerts.index')
                ->with('success', 'Alerta creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear alerta', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la alerta: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Mostrar detalles de alerta
    public function show(int $id): View
    {
        $alert = $this->alertService->getAlertById($id);

        if (!$alert) {
            abort(404);
        }

        return view('developer-web.alerts.show', compact('alert'));
    }

    // Mostrar formulario de edición
    public function edit(int $id): View
    {
        $alert = $this->alertService->getAlertById($id);

        if (!$alert) {
            abort(404);
        }

        return view('developer-web.alerts.edit', compact('alert'));
    }

    // Actualizar alerta
    public function update(UpdateAlertRequest $request, int $id): RedirectResponse
    {
        try {
            $success = $this->alertService->updateAlert($id, $request->validated());

            if ($success) {
                return redirect()->route('developer-web.alerts.index')
                    ->with('success', 'Alerta actualizada exitosamente.');
            }

            return redirect()->route('developer-web.alerts.index')
                ->with('error', 'No se pudo actualizar la alerta.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar alerta', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar la alerta: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Eliminar alerta
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
            Log::error('Error al eliminar alerta', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la alerta'
            ], 500);
        }
    }

    // API para frontend - Listar alertas activas
    public function apiIndex(): JsonResponse
    {
        try {
            $filters = [
                'type' => request('type'),
                'priority' => request('priority'),
            ];

            $alerts = $this->alertService->getAlertsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de alertas', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas'
            ], 500);
        }
    }

    // API para frontend - Mostrar alerta específica
    public function apiShow(int $id): JsonResponse
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
            Log::error('Error en API de alerta específica', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la alerta'
            ], 500);
        }
    }

    // Mostrar listado público de alertas activas
    public function publicIndex(): View
    {
        $alerts = $this->alertService->getActiveAlerts();

        return view('developer-web.alerts.public-index', compact('alerts'));
    }

    // Mostrar alertas de alta prioridad
    public function highPriority(): View
    {
        $alerts = $this->alertService->getHighPriorityAlerts();

        return view('developer-web.alerts.high-priority', compact('alerts'));
    }

    // API para alertas de alta prioridad
    public function apiHighPriority(): JsonResponse
    {
        try {
            $alerts = $this->alertService->getHighPriorityAlerts();

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de alertas de alta prioridad', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas de alta prioridad'
            ], 500);
        }
    }
}