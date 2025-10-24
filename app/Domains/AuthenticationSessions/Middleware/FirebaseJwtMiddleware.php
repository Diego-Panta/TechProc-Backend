<?php

namespace App\Domains\AuthenticationSessions\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Domains\AuthenticationSessions\Services\JwtService;

class FirebaseJwtMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Obtener token del header Authorization
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_MISSING',
                    'message' => 'Token de autenticación requerido'
                ]
            ], 401);
        }

        // Validar token
        $payload = $this->jwtService->validateToken($token);
        
        if (!$payload) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'Token inválido o expirado'
                ]
            ], 401);
        }

        // Buscar usuario en la base de datos
        $user = $this->jwtService->getUserFromToken($token);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_NOT_FOUND',
                    'message' => 'Usuario no encontrado'
                ]
            ], 404);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_INACTIVE',
                    'message' => 'Usuario inactivo'
                ]
            ], 403);
        }

        // Adjuntar usuario a la request
        $request->merge(['user' => $user]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }

    /**
     * Extraer token del header Authorization
     */
    private function getTokenFromRequest(Request $request)
    {
        $header = $request->header('Authorization', '');
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}