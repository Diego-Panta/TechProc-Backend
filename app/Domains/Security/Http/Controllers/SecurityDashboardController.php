<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Services\SecurityEventService;
use App\Domains\Security\Services\SessionService;
use App\Domains\Security\Services\TokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    public function __construct(
        private SessionService $sessionService,
        private TokenService $tokenService,
        private SecurityEventService $eventService
    ) {}

    /**
     * Dashboard de seguridad del usuario
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $user = $request->user();

        // Resúmenes
        $sessionsSummary = $this->sessionService->getSessionsSummary($userId);
        $tokensSummary = $this->tokenService->getTokensSummary($userId);
        $eventStats = $this->eventService->getStatistics($userId, 30);
        $criticalEvents = $this->eventService->getCriticalEvents($userId, 7);

        return response()->json([
            'success' => true,
            'data' => [
                // Info del usuario
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'has_2fa' => $user->two_factor_enabled ?? false,
                    'has_recovery_email' => !empty($user->recovery_email),
                    'recovery_email_verified' => !empty($user->recovery_email_verified_at),
                ],

                // Sesiones
                'sessions' => $sessionsSummary,

                // Tokens
                'tokens' => $tokensSummary,

                // Eventos
                'events' => [
                    'total_last_30_days' => $eventStats['total'],
                    'critical_count' => $eventStats['critical_count'],
                    'warning_count' => $eventStats['warning_count'],
                    'recent_critical' => \App\Domains\Security\Resources\SecurityEventResource::collection($criticalEvents),
                ],

                // Alertas
                'alerts' => [
                    'has_suspicious_sessions' => $sessionsSummary['has_suspicious'],
                    'has_inactive_tokens' => $tokensSummary['total_inactive'] > 0,
                    'has_critical_events' => $eventStats['critical_count'] > 0,
                ],
            ],
        ]);
    }
}
