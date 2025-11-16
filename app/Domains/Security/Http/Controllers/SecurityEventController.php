<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Resources\SecurityEventResource;
use App\Domains\Security\Services\SecurityEventService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityEventController extends Controller
{
    public function __construct(
        private SecurityEventService $eventService
    ) {}

    /**
     * Obtener mis eventos de seguridad
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $events = $this->eventService->getMyEvents($request->user()->id, $perPage);

        return response()->json([
            'success' => true,
            'data' => SecurityEventResource::collection($events),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ],
        ]);
    }

    /**
     * Obtener eventos recientes
     */
    public function recent(Request $request): JsonResponse
    {
        $days = $request->query('days', 7);
        $events = $this->eventService->getRecentEvents($request->user()->id, $days);

        return response()->json([
            'success' => true,
            'data' => SecurityEventResource::collection($events),
        ]);
    }

    /**
     * Obtener eventos críticos
     */
    public function critical(Request $request): JsonResponse
    {
        $days = $request->query('days', 7);
        $events = $this->eventService->getCriticalEvents($request->user()->id, $days);

        return response()->json([
            'success' => true,
            'data' => SecurityEventResource::collection($events),
        ]);
    }

    /**
     * Obtener estadísticas
     */
    public function statistics(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $stats = $this->eventService->getStatistics($request->user()->id, $days);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
