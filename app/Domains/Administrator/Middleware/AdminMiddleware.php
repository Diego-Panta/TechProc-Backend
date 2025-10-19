<?php

namespace App\Domains\Administrator\Middleware;

use Closure;
use App\Domains\AuthenticationSessions\Services\JwtService;
use App\Domains\Administrator\Models\User;

class AdminMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
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

            // Verificar que el usuario tenga rol de administrador
            if (!$this->isAdmin($user)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INSUFFICIENT_PERMISSIONS',
                        'message' => 'No tienes permisos de administrador para acceder a este recurso'
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
     * Verificar si el usuario es administrador
     */
    private function isAdmin(User $user)
    {
        // Verificar si el rol contiene 'admin'
        $roles = is_array($user->role) ? $user->role : [$user->role];
        return in_array('admin', $roles);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    private function hasRole(User $user, $role)
    {
        $roles = is_array($user->role) ? $user->role : [$user->role];
        return in_array($role, $roles);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    private function hasAnyRole(User $user, array $roles)
    {
        $userRoles = is_array($user->role) ? $user->role : [$user->role];
        return !empty(array_intersect($userRoles, $roles));
    }

    /**
     * Verificar si el usuario tiene todos los roles especificados
     */
    private function hasAllRoles(User $user, array $roles)
    {
        $userRoles = is_array($user->role) ? $user->role : [$user->role];
        return empty(array_diff($roles, $userRoles));
    }
}
