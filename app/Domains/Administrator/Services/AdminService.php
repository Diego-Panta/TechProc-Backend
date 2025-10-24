<?php

namespace App\Domains\Administrator\Services;

use App\Domains\Administrator\Models\User;
use App\Domains\Administrator\Models\Department;
use App\Domains\Administrator\Models\Position;
use App\Domains\Administrator\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    /**
     * Verificar si un usuario tiene relaciones que impidan su eliminación
     */
    public function userHasRelations(User $user)
    {
        // Verificar relaciones con otras tablas
        $relations = [
            'activeSessions',
            'securityLogs',
            'securityConfigurations',
            'instructor',
            'student',
            'employee',
            'groupParticipants',
            'evaluations',
            'attempts',
            'gradings',
            'gradeRecords',
            'finalGrades',
            'certificates',
            'diplomas',
            'tickets',
            'gradeChanges'
        ];

        foreach ($relations as $relation) {
            if ($user->$relation()->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function getUserStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
            'by_role' => User::selectRaw('JSON_UNQUOTE(JSON_EXTRACT(role, "$[0]")) as role, COUNT(*) as count')
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role')
                ->toArray(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count()
        ];

        return $stats;
    }

    /**
     * Obtener estadísticas de empleados
     */
    public function getEmployeeStats()
    {
        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('employment_status', 'active')->count(),
            'by_department' => Employee::join('departments', 'employees.department_id', '=', 'departments.id')
                ->selectRaw('departments.department_name, COUNT(*) as count')
                ->groupBy('departments.id', 'departments.department_name')
                ->get()
                ->pluck('count', 'department_name')
                ->toArray(),
            'by_position' => Employee::join('positions', 'employees.position_id', '=', 'positions.id')
                ->selectRaw('positions.position_name, COUNT(*) as count')
                ->groupBy('positions.id', 'positions.position_name')
                ->get()
                ->pluck('count', 'position_name')
                ->toArray()
        ];

        return $stats;
    }

    /**
     * Obtener estadísticas de departamentos
     */
    public function getDepartmentStats()
    {
        $stats = [
            'total_departments' => Department::count(),
            'departments_with_positions' => Department::has('positions')->count(),
            'departments_with_employees' => Department::has('employees')->count(),
            'positions_per_department' => Department::withCount('positions')->get()
                ->pluck('positions_count', 'department_name')
                ->toArray()
        ];

        return $stats;
    }

    /**
     * Buscar usuarios con filtros avanzados
     */
    public function searchUsers($filters = [])
    {
        $query = User::query();

        // Filtro por rol
        if (isset($filters['role']) && $filters['role']) {
            $query->whereJsonContains('role', $filters['role']);
        }

        // Filtro por estado
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Filtro por fecha de creación
        if (isset($filters['created_from']) && $filters['created_from']) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (isset($filters['created_to']) && $filters['created_to']) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        // Filtro por último acceso
        if (isset($filters['last_access_from']) && $filters['last_access_from']) {
            $query->where('last_access', '>=', $filters['last_access_from']);
        }

        if (isset($filters['last_access_to']) && $filters['last_access_to']) {
            $query->where('last_access', '<=', $filters['last_access_to']);
        }

        // Búsqueda por texto
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Crear usuario con validaciones adicionales
     */
    public function createUserWithValidation($userData)
    {
        // Validar que el email no esté en uso
        if (User::where('email', $userData['email'])->exists()) {
            throw new \Exception('El email ya está en uso');
        }

        // Validar que el rol sea válido
        $validRoles = ['admin', 'lms', 'seg', 'infra', 'web', 'data'];
        if (!in_array($userData['role'], $validRoles)) {
            throw new \Exception('Rol no válido');
        }

        // Crear usuario
        $user = User::create([
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'phone_number' => $userData['phone_number'] ?? null,
            'address' => $userData['address'] ?? null,
            'birth_date' => $userData['birth_date'] ?? null,
            'gender' => $userData['gender'] ?? null,
            'country' => $userData['country'] ?? null,
            'role' => [$userData['role']],
            'status' => $userData['status'] ?? 'active'
        ]);

        return $user;
    }

    /**
     * Actualizar usuario con validaciones
     */
    public function updateUserWithValidation($userId, $userData)
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        // Validar email único si se está cambiando
        if (isset($userData['email']) && $userData['email'] !== $user->email) {
            if (User::where('email', $userData['email'])->where('id', '!=', $userId)->exists()) {
                throw new \Exception('El email ya está en uso');
            }
        }

        // Validar rol si se está cambiando
        if (isset($userData['role'])) {
            $validRoles = ['admin', 'lms', 'seg', 'infra', 'web', 'data'];
            if (!in_array($userData['role'], $validRoles)) {
                throw new \Exception('Rol no válido');
            }
            $userData['role'] = [$userData['role']];
        }

        $user->update($userData);

        return $user;
    }

    /**
     * Obtener dashboard de administración
     */
    public function getAdminDashboard()
    {
        $userStats = $this->getUserStats();
        $employeeStats = $this->getEmployeeStats();
        $departmentStats = $this->getDepartmentStats();

        return [
            'users' => $userStats,
            'employees' => $employeeStats,
            'departments' => $departmentStats,
            'recent_activities' => $this->getRecentActivities(),
            'system_health' => $this->getSystemHealth()
        ];
    }

    /**
     * Obtener actividades recientes
     */
    private function getRecentActivities()
    {
        // Obtener usuarios creados recientemente
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user_created',
                    'description' => "Nuevo usuario: {$user->first_name} {$user->last_name}",
                    'timestamp' => $user->created_at->toISOString()
                ];
            });

        // Obtener empleados creados recientemente
        $recentEmployees = Employee::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'type' => 'employee_created',
                    'description' => "Nuevo empleado: {$employee->user->first_name} {$employee->user->last_name}",
                    'timestamp' => $employee->created_at->toISOString()
                ];
            });

        return $recentUsers->merge($recentEmployees)->sortByDesc('timestamp')->take(10)->values();
    }

    /**
     * Obtener estado del sistema
     */
    private function getSystemHealth()
    {
        return [
            'database_connection' => $this->checkDatabaseConnection(),
            'active_sessions' => \App\Domains\AuthenticationSessions\Models\ActiveSession::where('active', true)->count(),
            'pending_registrations' => User::where('status', 'inactive')->count(),
            'system_uptime' => $this->getSystemUptime()
        ];
    }

    /**
     * Verificar conexión a la base de datos
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener tiempo de actividad del sistema
     */
    private function getSystemUptime()
    {
        try {
            $uptime = shell_exec('uptime');
            return trim($uptime);
        } catch (\Exception $e) {
            return 'No disponible';
        }
    }

    /**
     * Exportar datos de usuarios
     */
    public function exportUsers($filters = [])
    {
        $query = $this->searchUsers($filters);
        
        return $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => is_array($user->role) ? implode(',', $user->role) : $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'last_access' => $user->last_access ? $user->last_access->format('Y-m-d H:i:s') : null
            ];
        });
    }

    /**
     * Obtener logs de auditoría de usuarios
     */
    public function getUserAuditLogs($userId, $limit = 50)
    {
        // Esta función podría integrarse con un sistema de logs de auditoría
        // Por ahora retornamos un array vacío
        return [];
    }
}
