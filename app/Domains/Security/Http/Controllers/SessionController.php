<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Models\UserSession;
use App\Domains\Security\Models\ActiveToken;
use App\Domains\Security\Resources\SessionResource;
use App\Domains\Security\Services\SessionService;
use App\Domains\Security\Services\SecurityEventService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(
        private SessionService $sessionService,
        private SecurityEventService $eventService
    ) {}

    /**
     * Obtener sesiones activas
     * - Usuario normal: Solo SUS sesiones
     * - Rol security: Sesiones de un usuario específico (requiere ?user_id=X)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Si es rol security y pasa user_id, ver sesiones de ese usuario
        if ($user->hasPermissionTo('sessions.view-any') && $request->has('user_id')) {
            $userId = $request->query('user_id');
        } else {
            // Usuario normal: ver solo sus propias sesiones
            $userId = $user->id;
        }

        $sessions = $this->sessionService->getMySessions($userId);

        return response()->json([
            'success' => true,
            'data' => SessionResource::collection($sessions),
            'user_id' => $userId,
        ]);
    }

    /**
     * Obtener TODAS las sesiones activas del sistema (solo rol security)
     */
    public function all(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('sessions.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver sesiones de todos los usuarios'
            ], 403);
        }

        // Obtener tokens activos (sesiones) de todos los usuarios
        $tokens = ActiveToken::with('user')
            ->active()
            ->orderBy('last_used_at', 'desc')
            ->get();

        // Agrupar por usuario
        $groupedByUser = $tokens->groupBy('tokenable_id')->map(function ($userTokens, $userId) {
            $firstToken = $userTokens->first();
            $user = $firstToken->user;

            return [
                'user_id' => $userId,
                'user_name' => $user->name ?? 'Unknown',
                'user_email' => $user->email ?? 'Unknown',
                'total_sessions' => $userTokens->count(),
                'unique_ips' => $userTokens->pluck('ip_address')->unique()->filter()->count(),
                'sessions' => $userTokens->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'ip_address' => $token->ip_address,
                        'device' => $token->device,
                        'last_activity_human' => $token->last_activity_human,
                        'is_active' => $token->is_active,
                        'is_current' => false, // En API con tokens no hay "sesión actual"
                        'created_at' => $token->created_at->toIso8601String(),
                        'last_used_at' => $token->last_used_at?->toIso8601String(),
                    ];
                }),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $groupedByUser,
            'total_users' => $groupedByUser->count(),
            'total_sessions' => $tokens->count(),
        ]);
    }

    /**
     * Obtener sesiones sospechosas
     */
    public function suspicious(Request $request): JsonResponse
    {
        $user = $request->user();

        // Si es rol security, ver sesiones sospechosas de TODOS
        if ($user->hasPermissionTo('sessions.view-any')) {
            $allUsers = \App\Models\User::all();
            $allSuspicious = [];

            foreach ($allUsers as $checkUser) {
                $sessions = $this->sessionService->getSuspiciousSessions($checkUser->id);
                if ($sessions->isNotEmpty()) {
                    $allSuspicious[] = [
                        'user_id' => $checkUser->id,
                        'user_name' => $checkUser->name,
                        'user_email' => $checkUser->email,
                        'sessions' => SessionResource::collection($sessions),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $allSuspicious,
                'total_users_with_suspicious' => count($allSuspicious),
            ]);
        }

        // Usuario normal: solo sus sesiones sospechosas
        $sessions = $this->sessionService->getSuspiciousSessions($user->id);

        return response()->json([
            'success' => true,
            'data' => SessionResource::collection($sessions),
            'has_suspicious' => $sessions->isNotEmpty(),
        ]);
    }

    /**
     * Terminar una sesión específica (revocar token)
     */
    public function destroy(Request $request, int $sessionId): JsonResponse
    {
        $token = ActiveToken::find($sessionId);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada'
            ], 404);
        }

        // Verificar permisos
        if ($token->tokenable_id !== $request->user()->id &&
            !$request->user()->hasPermissionTo('sessions.terminate-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para terminar esta sesión'
            ], 403);
        }

        $result = $this->sessionService->terminateSession($sessionId, $token->tokenable_id);

        if ($result['success']) {
            // Registrar evento
            $this->eventService->logSessionTerminated(
                $request->user()->id,
                $token->ip_address,
                $request->ip(),
                $request->userAgent()
            );
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Terminar todas las sesiones (tokens) excepto el actual
     */
    public function terminateAll(Request $request): JsonResponse
    {
        try {
            // Obtener el token actual del usuario autenticado
            $currentToken = $request->user()->currentAccessToken();
            $currentTokenId = $currentToken ? $currentToken->id : 0;

            $userId = $request->query('user_id', $request->user()->id);

            // Verificar permisos
            if ($userId != $request->user()->id &&
                !$request->user()->hasPermissionTo('sessions.terminate-any')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para terminar sesiones de otros usuarios'
                ], 403);
            }

            $result = $this->sessionService->terminateAllExceptCurrent($userId, $currentTokenId);

            // Registrar evento (con try-catch para evitar que errores de logging rompan la funcionalidad)
            try {
                $this->eventService->logEvent(
                    $request->user()->id,
                    \App\Domains\Security\Enums\SecurityEventType::SESSION_TERMINATED,
                    \App\Domains\Security\Enums\SecurityEventSeverity::INFO,
                    $request->ip(),
                    $request->userAgent(),
                    ['action' => 'terminate_all', 'count' => $result['count'], 'target_user_id' => $userId]
                );
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::warning('Failed to log security event', [
                    'error' => $e->getMessage(),
                    'user_id' => $request->user()->id,
                    'target_user_id' => $userId
                ]);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al terminar sesiones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
