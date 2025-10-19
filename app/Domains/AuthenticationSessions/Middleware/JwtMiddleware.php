<?php

namespace App\Domains\AuthenticationSessions\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'Usuario no encontrado'
                    ]
                ], 404);
            }

            // Check if user is active
            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_INACTIVE', 
                        'message' => 'Usuario inactivo'
                    ]
                ], 403);
            }

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'Token inválido'
                ]
            ], 401);
        }

        return $next($request);
    }
}