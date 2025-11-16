<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Resources\TokenResource;
use App\Domains\Security\Services\SecurityEventService;
use App\Domains\Security\Services\TokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function __construct(
        private TokenService $tokenService,
        private SecurityEventService $eventService
    ) {}

    /**
     * Obtener mis tokens activos
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $this->tokenService->getMyTokens($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => TokenResource::collection($tokens),
        ]);
    }

    /**
     * Obtener tokens inactivos
     */
    public function inactive(Request $request): JsonResponse
    {
        $days = $request->query('days', 30);
        $tokens = $this->tokenService->getInactiveTokens($request->user()->id, $days);

        return response()->json([
            'success' => true,
            'data' => TokenResource::collection($tokens),
        ]);
    }

    /**
     * Revocar un token
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $result = $this->tokenService->revokeToken($tokenId, $request->user()->id);

        if ($result['success']) {
            // Registrar evento
            $this->eventService->logTokenRevoked(
                $request->user()->id,
                "Token ID: $tokenId",
                $request->ip(),
                $request->userAgent()
            );
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Revocar todos los tokens
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $result = $this->tokenService->revokeAllTokens($request->user()->id);

        // Registrar evento
        $this->eventService->logEvent(
            $request->user()->id,
            \App\Domains\Security\Enums\SecurityEventType::TOKEN_REVOKED,
            \App\Domains\Security\Enums\SecurityEventSeverity::INFO,
            $request->ip(),
            $request->userAgent(),
            ['action' => 'revoke_all', 'count' => $result['count']]
        );

        return response()->json($result);
    }
}
