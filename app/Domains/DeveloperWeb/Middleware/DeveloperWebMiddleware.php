<?php

namespace App\Domains\DeveloperWeb\Middleware;

use Closure;
use App\Domains\AuthenticationSessions\Services\JwtService;
use App\Domains\AuthenticationSessions\Models\User;

class DeveloperWebMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        try {
            // Obtener token del header Authorization
            $token = $this->getTokenFromRequest($request);
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'TOKEN_MISSING',
                        'message' => 'Token de autorización no proporcionado'
                    ]
                ], 401);
            }

            // Validar token JWT
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

            // Obtener usuario desde el token
            $userId = $payload['sub'] ?? null;
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'Usuario no encontrado en el token'
                    ]
                ], 401);
            }

            // Buscar usuario en la base de datos
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'Usuario no encontrado'
                    ]
                ], 404);
            }

            // Verificar que el usuario esté activo
            if (!$this->isUserActive($user)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_INACTIVE',
                        'message' => 'Usuario inactivo. Contacte al administrador.'
                    ]
                ], 403);
            }

            // Verificar que el usuario tenga rol de developer web
            if (!$this->hasDeveloperWebRole($user)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INSUFFICIENT_PERMISSIONS',
                        'message' => 'No tienes permisos de Developer Web para acceder a este recurso'
                    ]
                ], 403);
            }

            // Agregar usuario autenticado al request
            $request->merge(['authenticated_user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_ERROR',
                    'message' => 'Error de autenticación: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Extraer token del header Authorization
     */
    private function getTokenFromRequest($request)
    {
        $header = $request->header('Authorization', '');
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Verificar si el usuario está activo
     */
    private function isUserActive(User $user)
    {
        return $user->status === 'active';
    }

    /**
     * Verificar si el usuario tiene rol de developer web
     */
    private function hasDeveloperWebRole(User $user)
    {
        $roles = is_array($user->role) ? $user->role : [$user->role];
        return in_array('web', $roles) || in_array('admin', $roles);
    }

    /**
     * Obtener el empleado asociado al usuario (para asignación automática)
     */
    private function getEmployeeFromUser(User $user)
    {
        return $user->employee;
    }
}