<?php

namespace App\Domains\AuthenticationSessions\Controllers;

use App\Domains\AuthenticationSessions\Models\User;
use App\Domains\AuthenticationSessions\Models\ActiveSession;
use App\Domains\AuthenticationSessions\Services\JwtService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

        // Cargar datos del empleado si existe
        $user->load(['employee.position', 'employee.department']);

        $response = [
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
        ];

        // Agregar datos del empleado si existe
        if ($user->employee) {
            $response['data']['employee'] = [
                'id' => $user->employee->id,
                'employee_id' => $user->employee->employee_id,
                'hire_date' => $user->employee->hire_date,
                'position' => $user->employee->position ? [
                    'id' => $user->employee->position->id,
                    'position_name' => $user->employee->position->position_name,
                    'department_id' => $user->employee->position->department_id
                ] : null,
                'department' => $user->employee->department ? [
                    'id' => $user->employee->department->id,
                    'department_name' => $user->employee->department->department_name
                ] : null,
                'employment_status' => $user->employee->employment_status,
                'schedule' => $user->employee->schedule,
                'speciality' => $user->employee->speciality,
                'salary' => $user->employee->salary
            ];
        }

        return response()->json($response, 200);
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
            'role' => 'required|in:admin,lms,seg,infra,web,data,support',
            'reason' => 'required|string|max:500',
            // Datos del empleado
            'position_id' => 'required|integer',
            'department_id' => 'required|integer',
            'hire_date' => 'nullable|date',
            'employment_status' => 'nullable|in:Active,Inactive,Terminated',
            'schedule' => 'nullable|string',
            'speciality' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0'
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

        // Validación manual de existencia de position y department
        $position = \App\Domains\Administrator\Models\Position::find($request->position_id);
        $department = \App\Domains\Administrator\Models\Department::find($request->department_id);

        $errors = [];
        if (!$position) {
            $errors['position_id'] = ['El ID de posición proporcionado no existe.'];
        }
        if (!$department) {
            $errors['department_id'] = ['El ID de departamento proporcionado no existe.'];
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Error de validación en los datos enviados',
                    'details' => $errors
                ]
            ], 422);
        }

        try {
            // Iniciar transacción para asegurar integridad de datos
            DB::beginTransaction();

            // Crear usuario
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'role' => [$request->role],
                'status' => 'inactive'
            ]);

            // Crear empleado asociado
            $employee = \App\Domains\Administrator\Models\Employee::create([
                'user_id' => $user->id,
                'position_id' => $request->position_id,
                'department_id' => $request->department_id,
                'hire_date' => $request->hire_date ?? now(),
                'employment_status' => $request->employment_status ?? 'Active',
                'schedule' => $request->schedule,
                'speciality' => $request->speciality,
                'salary' => $request->salary
            ]);

            // Confirmar transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de registro enviada. Será revisada por un administrador.',
                'data' => [
                    'request_id' => $user->id,
                    'employee_id' => $employee->id
                ]
            ], 201);

        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'REGISTRATION_ERROR',
                    'message' => 'Error al crear el registro: ' . $e->getMessage()
                ]
            ], 500);
        }
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