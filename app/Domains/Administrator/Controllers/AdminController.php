<?php

namespace App\Domains\Administrator\Controllers;

use App\Domains\Administrator\Models\User;
use App\Domains\Administrator\Models\Department;
use App\Domains\Administrator\Models\Position;
use App\Domains\Administrator\Models\Employee;
use App\Domains\Administrator\Services\AdminService;
use App\Domains\Administrator\Middleware\AdminMiddleware;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * 2.1.1. Listar Todos los Usuarios
     * GET /admin/users
     */
    public function getUsers(Request $request)
    {
        try {
            $query = User::query();

            // Filtros
            if ($request->has('role') && $request->role) {
                $query->whereJsonContains('role', $request->role);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Obtener todos los usuarios (sin paginación por defecto)
            $users = $query->get();

            $usersData = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'dni' => $user->dni,
                    'document' => $user->document,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
                    'phone_number' => $user->phone_number,
                    'address' => $user->address,
                    'birth_date' => $user->birth_date ? $user->birth_date->format('Y-m-d') : null,
                    'role' => $user->role,
                    'gender' => $user->gender,
                    'country' => $user->country,
                    'country_location' => $user->country_location,
                    'timezone' => $user->timezone,
                    'profile_photo' => $user->profile_photo,
                    'status' => $user->status,
                    'synchronized' => $user->synchronized,
                    'last_access_ip' => $user->last_access_ip,
                    'last_access' => $user->last_access ? $user->last_access->toISOString() : null,
                    'last_connection' => $user->last_connection ? $user->last_connection->toISOString() : null,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usersData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.1.2. Obtener Detalles de Usuario
     * GET /admin/users/{user_id}
     */
    public function getUser($userId)
    {
        try {
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

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'address' => $user->address,
                    'birth_date' => $user->birth_date ? $user->birth_date->format('Y-m-d') : null,
                    'gender' => $user->gender,
                    'country' => $user->country,
                    'role' => $user->role,
                    'status' => $user->status,
                    'profile_photo' => $user->profile_photo,
                    'last_access' => $user->last_access ? $user->last_access->toISOString() : null,
                    'last_access_ip' => $user->last_access_ip,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.1.3. Crear Usuario
     * POST /admin/users
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'full_name' => 'nullable|string|max:100',
            'dni' => 'nullable|string|max:20|unique:users',
            'document' => 'nullable|string|max:20|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'country' => 'nullable|string|max:100',
            'country_location' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|string|max:500',
            'role' => 'required|in:admin,instructor,student,lms,seg,infra,web,data',
            'status' => 'nullable|in:active,inactive,banned',
            'synchronized' => 'nullable|boolean'
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
            DB::beginTransaction();

            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'full_name' => $request->full_name ?? ($request->first_name . ' ' . $request->last_name),
                'dni' => $request->dni,
                'document' => $request->document,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'country' => $request->country,
                'country_location' => $request->country_location,
                'timezone' => $request->timezone ?? 'America/Lima',
                'profile_photo' => $request->profile_photo,
                'role' => [$request->role],
                'status' => $request->status ?? 'active',
                'synchronized' => $request->synchronized ?? true
            ];

            $user = User::create($userData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.1.4. Actualizar Usuario
     * PUT /admin/users/{user_id}
     */
    public function updateUser(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'full_name' => 'nullable|string|max:100',
            'dni' => 'nullable|string|max:20|unique:users,dni,' . $userId,
            'document' => 'nullable|string|max:20|unique:users,document,' . $userId,
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'country' => 'nullable|string|max:100',
            'country_location' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|string|max:500',
            'role' => 'sometimes|required|in:admin,instructor,student,lms,seg,infra,web,data',
            'status' => 'sometimes|required|in:active,inactive,banned',
            'synchronized' => 'nullable|boolean'
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

            $updateData = $request->only([
                'first_name', 'last_name', 'full_name', 'dni', 'document', 'email',
                'phone_number', 'address', 'birth_date', 'gender', 'country',
                'country_location', 'timezone', 'profile_photo', 'status', 'synchronized'
            ]);

            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            if ($request->has('role')) {
                $updateData['role'] = [$request->role];
            }

            // Auto-generar full_name si se actualizan first_name o last_name
            if ($request->has('first_name') || $request->has('last_name')) {
                $firstName = $request->first_name ?? $user->first_name;
                $lastName = $request->last_name ?? $user->last_name;
                $updateData['full_name'] = $updateData['full_name'] ?? ($firstName . ' ' . $lastName);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.1.5. Eliminar Usuario
     * DELETE /admin/users/{user_id}
     */
    public function deleteUser($userId)
    {
        try {
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

            // Iniciar transacción para asegurar integridad
            DB::beginTransaction();

            try {
                // Eliminar empleado asociado si existe
                $employee = Employee::where('user_id', $userId)->first();
                if ($employee) {
                    $employee->delete();
                }

                // Eliminar sesiones activas
                DB::table('active_sessions')->where('user_id', $userId)->delete();

                // Eliminar el usuario
                $user->delete();

                // Confirmar transacción
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuario y datos relacionados eliminados exitosamente'
                ], 200);

            } catch (\Exception $e) {
                // Revertir transacción en caso de error
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.2.1. Listar Solicitudes Pendientes
     * GET /admin/registration-requests
     */
    public function getRegistrationRequests(Request $request)
    {
        try {
            $query = User::where('status', 'inactive');

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $requests = $query->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'reason' => $user->reason ?? 'No especificado',
                    'created_at' => $user->created_at->toISOString(),
                    'status' => $user->status
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $requests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.2.2. Aprobar Solicitud de Registro
     * POST /admin/registration-requests/{request_id}/approve
     */
    public function approveRegistrationRequest(Request $request, $requestId)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,lms,seg,infra,web,data,support',
            'status' => 'required|in:active,inactive,banned'
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
            $user = User::find($requestId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'Usuario no encontrado'
                    ]
                ], 404);
            }

            $user->update([
                'role' => [$request->role],
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada. Usuario activado.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * 2.2.3. Rechazar Solicitud de Registro
     * POST /admin/registration-requests/{request_id}/reject
     */
    public function rejectRegistrationRequest(Request $request, $requestId)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500'
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
            $user = User::find($requestId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'Usuario no encontrado'
                    ]
                ], 404);
            }

            $user->update([
                'status' => 'banned',
                'rejection_reason' => $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Gestión de Departamentos
     */
    public function getDepartments()
    {
        try {
            $departments = Department::with('positions')->get();

            return response()->json([
                'success' => true,
                'data' => $departments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function createDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500'
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
            $department = Department::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Departamento creado exitosamente',
                'data' => $department
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Gestión de Posiciones
     */
    public function getPositions()
    {
        try {
            $positions = Position::with('department')->get();

            return response()->json([
                'success' => true,
                'data' => $positions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function createPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id'
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
            $position = Position::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Posición creada exitosamente',
                'data' => $position
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Gestión de Empleados
     */
    public function getEmployees(Request $request)
    {
        try {
            $query = Employee::with(['user', 'position', 'department']);

            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->has('position_id')) {
                $query->where('position_id', $request->position_id);
            }

            $employees = $query->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function createEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'position_id' => 'required|exists:positions,id',
            'department_id' => 'required|exists:departments,id',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:Active,Inactive,Terminated',
            'salary' => 'nullable|numeric|min:0',
            'speciality' => 'nullable|string',
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

            $data = $request->all();
            if (isset($data['employment_status'])) {
                $data['employment_status'] = ucfirst($data['employment_status']);
            }
            
            $employee = Employee::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'data' => $employee
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Métodos adicionales para completar las rutas
     */
    
    // Dashboard
    public function getDashboard()
    {
        try {
            $dashboard = $this->adminService->getAdminDashboard();
            
            return response()->json([
                'success' => true,
                'data' => $dashboard
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Estadísticas
    public function getUserStats()
    {
        try {
            $stats = $this->adminService->getUserStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getEmployeeStats()
    {
        try {
            $stats = $this->adminService->getEmployeeStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getDepartmentStats()
    {
        try {
            $stats = $this->adminService->getDepartmentStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de departamentos
    public function getDepartment($departmentId)
    {
        try {
            $department = Department::with('positions')->find($departmentId);
            
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DEPARTMENT_NOT_FOUND',
                        'message' => 'Departamento no encontrado'
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $department
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function updateDepartment(Request $request, $departmentId)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500'
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
            $department = Department::find($departmentId);
            
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DEPARTMENT_NOT_FOUND',
                        'message' => 'Departamento no encontrado'
                    ]
                ], 404);
            }

            $department->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Departamento actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function deleteDepartment($departmentId)
    {
        try {
            $department = Department::find($departmentId);
            
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DEPARTMENT_NOT_FOUND',
                        'message' => 'Departamento no encontrado'
                    ]
                ], 404);
            }

            // Verificar si tiene posiciones asociadas
            if ($department->positions()->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DEPARTMENT_HAS_POSITIONS',
                        'message' => 'No se puede eliminar el departamento porque tiene posiciones asociadas'
                    ]
                ], 400);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Departamento eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de posiciones
    public function getPosition($positionId)
    {
        try {
            $position = Position::with('department')->find($positionId);
            
            if (!$position) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'POSITION_NOT_FOUND',
                        'message' => 'Posición no encontrada'
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $position
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function updatePosition(Request $request, $positionId)
    {
        $validator = Validator::make($request->all(), [
            'position_name' => 'sometimes|required|string|max:100',
            'department_id' => 'sometimes|required|exists:departments,id'
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
            $position = Position::find($positionId);
            
            if (!$position) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'POSITION_NOT_FOUND',
                        'message' => 'Posición no encontrada'
                    ]
                ], 404);
            }

            $position->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Posición actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function deletePosition($positionId)
    {
        try {
            $position = Position::find($positionId);
            
            if (!$position) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'POSITION_NOT_FOUND',
                        'message' => 'Posición no encontrada'
                    ]
                ], 404);
            }

            // Verificar si tiene empleados asociados
            if ($position->employees()->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'POSITION_HAS_EMPLOYEES',
                        'message' => 'No se puede eliminar la posición porque tiene empleados asociados'
                    ]
                ], 400);
            }

            $position->delete();

            return response()->json([
                'success' => true,
                'message' => 'Posición eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de empleados
    public function getEmployee($employeeId)
    {
        try {
            $employee = Employee::with(['user', 'position', 'department'])->find($employeeId);
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'EMPLOYEE_NOT_FOUND',
                        'message' => 'Empleado no encontrado'
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $employee
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function updateEmployee(Request $request, $employeeId)
    {
        $validator = Validator::make($request->all(), [
            'position_id' => 'sometimes|required|exists:positions,id',
            'department_id' => 'sometimes|required|exists:departments,id',
            'hire_date' => 'sometimes|required|date',
            'employment_status' => 'sometimes|required|in:active,inactive,terminated',
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

        try {
            $employee = Employee::find($employeeId);
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'EMPLOYEE_NOT_FOUND',
                        'message' => 'Empleado no encontrado'
                    ]
                ], 404);
            }

            $employee->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function deleteEmployee($employeeId)
    {
        try {
            $employee = Employee::find($employeeId);
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'EMPLOYEE_NOT_FOUND',
                        'message' => 'Empleado no encontrado'
                    ]
                ], 404);
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de exportación
    public function exportUsers(Request $request)
    {
        try {
            $filters = $request->all();
            $users = $this->adminService->exportUsers($filters);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function exportEmployees(Request $request)
    {
        try {
            $employees = Employee::with(['user', 'position', 'department'])->get();
            
            $exportData = $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'user_name' => $employee->user->first_name . ' ' . $employee->user->last_name,
                    'email' => $employee->user->email,
                    'position' => $employee->position->position_name,
                    'department' => $employee->department->department_name,
                    'hire_date' => $employee->hire_date->format('Y-m-d'),
                    'employment_status' => $employee->employment_status,
                    'salary' => $employee->salary
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $exportData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de auditoría
    public function getUserAuditLogs($userId, Request $request)
    {
        try {
            $limit = $request->get('limit', 50);
            $logs = $this->adminService->getUserAuditLogs($userId, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $logs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getSystemLogs(Request $request)
    {
        try {
            // Implementar lógica para obtener logs del sistema
            return response()->json([
                'success' => true,
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de configuración
    public function getSettings()
    {
        try {
            // Implementar lógica para obtener configuraciones
            return response()->json([
                'success' => true,
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            // Implementar lógica para actualizar configuraciones
            return response()->json([
                'success' => true,
                'message' => 'Configuraciones actualizadas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de roles
    public function getRoles()
    {
        try {
            $roles = ['admin', 'lms', 'seg', 'infra', 'web', 'data'];
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function assignRole(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,lms,seg,infra,web,data'
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

            $currentRoles = is_array($user->role) ? $user->role : [$user->role];
            
            if (!in_array($request->role, $currentRoles)) {
                $currentRoles[] = $request->role;
                $user->update(['role' => $currentRoles]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol asignado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function removeRole($userId, $role)
    {
        try {
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

            $currentRoles = is_array($user->role) ? $user->role : [$user->role];
            $updatedRoles = array_diff($currentRoles, [$role]);
            
            if (empty($updatedRoles)) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CANNOT_REMOVE_LAST_ROLE',
                        'message' => 'No se puede remover el último rol del usuario'
                    ]
                ], 400);
            }

            $user->update(['role' => array_values($updatedRoles)]);

            return response()->json([
                'success' => true,
                'message' => 'Rol removido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de sesiones
    public function getActiveSessions(Request $request)
    {
        try {
            $sessions = \App\Domains\AuthenticationSessions\Models\ActiveSession::with('user')
                ->where('active', true)
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'user' => [
                            'id' => $session->user->id,
                            'name' => $session->user->first_name . ' ' . $session->user->last_name,
                            'email' => $session->user->email
                        ],
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function terminateSession($sessionId)
    {
        try {
            $session = \App\Domains\AuthenticationSessions\Models\ActiveSession::find($sessionId);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'SESSION_NOT_FOUND',
                        'message' => 'Sesión no encontrada'
                    ]
                ], 404);
            }

            $session->update(['active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Sesión terminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function blockSession($sessionId)
    {
        try {
            $session = \App\Domains\AuthenticationSessions\Models\ActiveSession::find($sessionId);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'SESSION_NOT_FOUND',
                        'message' => 'Sesión no encontrada'
                    ]
                ], 404);
            }

            $session->update(['blocked' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Sesión bloqueada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Métodos de notificaciones
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,error,success'
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
            // Implementar lógica para enviar notificaciones
            return response()->json([
                'success' => true,
                'message' => 'Notificación enviada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getNotifications(Request $request)
    {
        try {
            // Implementar lógica para obtener notificaciones
            return response()->json([
                'success' => true,
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function markNotificationAsRead($notificationId)
    {
        try {
            // Implementar lógica para marcar notificación como leída
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}
