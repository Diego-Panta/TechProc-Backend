<?php

namespace App\Domains\AuthenticationSessions\Controllers;

use App\Domains\AuthenticationSessions\Models\User;
use App\Domains\AuthenticationSessions\Models\ActiveSession;
use App\Domains\AuthenticationSessions\Services\JwtService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Error de validación en los datos enviados',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        // Buscar usuario por email
        $user = User::where('email', $request->email)->first();

        // Verificar credenciales
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_FAILED',
                    'message' => 'Email o contraseña incorrectos'
                ]
            ], 401);
        }

        // Verificar que el usuario esté activo
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'USER_INACTIVE',
                    'message' => 'Usuario inactivo. Contacte al administrador.'
                ]
            ], 403);
        }

        $token = $this->jwtService->generateToken($user);
        
        // Crear sesión activa
        $session = ActiveSession::create([
            'user_id' => $user->id,
            'session_id' => time(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $this->getDevice($request->userAgent()),
            'start_date' => now(),
            'active' => true,
            'blocked' => false
        ]);

        // Actualizar último acceso del usuario
        $user->update([
            'last_access' => now(),
            'last_access_ip' => $request->ip(),
            'last_connection' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_photo' => $user->profile_photo,
                    'status' => $user->status
                ],
                'session' => [
                    'session_id' => $session->id,
                    'token' => $token,
                    'expires_at' => now()->addHours(2)->toISOString()
                ]
            ]
        ], 200);
    }

    /**
     * Verificar token
    */
    public function verifyToken(Request $request)
    {
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_MISSING',
                    'message' => 'Token no proporcionado'
                ]
            ], 401);
        }

        $payload = $this->jwtService->validateToken($token);
        
        if (!$payload) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'Token inválido'
                ]
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'valid' => true,
                'user' => $payload['user']
            ]
        ], 200);
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'role' => 'required|in:admin,lms,seg,infra,web,data',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Error de validación en los datos enviados',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'role' => [$request->role],
            'status' => 'inactive'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de registro enviada. Será revisada por un administrador.',
            'data' => [
                'request_id' => $user->id
            ]
        ], 201);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Error de validación en los datos enviados',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        try {
            $session = ActiveSession::find($request->session_id);
            
            if ($session) {
                $session->update([
                    'active' => false
                ]);
            }

            // ✅ ELIMINADO: JWTAuth::invalidate(JWTAuth::getToken());
            // Con Firebase JWT, los tokens no se invalidan del lado del servidor
            // Se manejan por expiración automática

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'LOGOUT_ERROR',
                    'message' => 'Error al cerrar sesión: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Get user active sessions
     */
    public function getSessions($userId)
    {
        // Verificar que el usuario autenticado tenga permisos
        $authenticatedUser = request()->user();
        
        if ($authenticatedUser->id != $userId && !$authenticatedUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'No tienes permisos para ver estas sesiones'
                ]
            ], 403);
        }

        $sessions = ActiveSession::where('user_id', $userId)
            ->where('active', true)
            ->get()
            ->map(function ($session) {
                return [
                    'session_id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'device' => $session->device,
                    'start_date' => $session->start_date->toISOString(),
                    'active' => $session->active,
                    'blocked' => $session->blocked
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions
        ], 200);
    }

    private function getDevice($userAgent)
    {
        if (strpos($userAgent, 'Mobile') !== false) {
            return 'Mobile';
        } elseif (strpos($userAgent, 'Tablet') !== false) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    private function getTokenFromRequest(Request $request)
    {
        $header = $request->header('Authorization', '');
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}