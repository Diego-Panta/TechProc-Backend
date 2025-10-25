<?php

namespace App\Domains\SupportInfrastructure\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Models\Employee;
use App\Domains\AuthenticationSessions\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     * Filters: ?department_id=3&position_id=5&employment_status=Active&user_id=10
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'position', 'department']);

        // Filtrar por department_id
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filtrar por position_id
        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        // Filtrar por employment_status
        if ($request->has('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        // Filtrar por user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrar por employee_id
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|integer|unique:employees,employee_id',
            'hire_date' => 'nullable|date',
            'position_id' => 'required|integer|exists:positions,id',
            'department_id' => 'required|integer|exists:departments,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'employment_status' => 'nullable|string|in:Active,Inactive,Terminated',
            'schedule' => 'nullable|string',
            'speciality' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric',
            'created_at' => 'nullable|date',
        ]);

        $employee = Employee::create($data);
        return response()->json($employee, 201);
    }

    /**
     * Create a new employee with a new user in one operation.
     * POST /api/infraestructura/employees/create-with-user
     */
    public function createWithUser(Request $request)
    {
        $data = $request->validate([
            // Datos del usuario
            'user.first_name' => 'required|string|max:100',
            'user.last_name' => 'required|string|max:100',
            'user.dni' => 'nullable|string|max:20|unique:users,dni',
            'user.document' => 'nullable|string|max:20|unique:users,document',
            'user.email' => 'required|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:6',
            'user.phone_number' => 'nullable|string|max:20',
            'user.address' => 'nullable|string',
            'user.birth_date' => 'nullable|date',
            'user.role' => 'nullable|array',
            'user.gender' => 'nullable|string|in:male,female,other',
            'user.country' => 'nullable|string|max:100',
            'user.country_location' => 'nullable|string|max:100',
            'user.timezone' => 'nullable|string|max:50',
            'user.profile_photo' => 'nullable|string|max:500',
            'user.status' => 'nullable|string|in:active,inactive,banned',

            // Datos del empleado
            'employee.employee_id' => 'nullable|integer|unique:employees,employee_id',
            'employee.hire_date' => 'nullable|date',
            'employee.position_id' => 'required|integer|exists:positions,id',
            'employee.department_id' => 'required|integer|exists:departments,id',
            'employee.employment_status' => 'nullable|string|in:Active,Inactive,Terminated',
            'employee.schedule' => 'nullable|string',
            'employee.speciality' => 'nullable|string|max:255',
            'employee.salary' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Crear el usuario
            $userData = $data['user'];
            $userData['password'] = Hash::make($userData['password']);
            $userData['full_name'] = $userData['first_name'] . ' ' . $userData['last_name'];

            // Valores por defecto
            $userData['status'] = $userData['status'] ?? 'active';
            $userData['timezone'] = $userData['timezone'] ?? 'America/Lima';
            $userData['synchronized'] = true;

            $user = User::create($userData);

            // Crear el empleado asociado al usuario
            $employeeData = $data['employee'];
            $employeeData['user_id'] = $user->id;

            $employee = Employee::create($employeeData);

            DB::commit();

            // Cargar relaciones para la respuesta
            $employee->load(['user', 'position', 'department']);

            return response()->json([
                'success' => true,
                'message' => 'Employee and user created successfully',
                'data' => $employee
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error creating employee and user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     */
    public function show($id)
    {
        return response()->json(
            Employee::with(['user', 'position', 'department'])->findOrFail($id)
        );
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'employee_id' => 'sometimes|integer|unique:employees,employee_id,' . $id,
            'hire_date' => 'sometimes|date',
            'position_id' => 'sometimes|integer|exists:positions,id',
            'department_id' => 'sometimes|integer|exists:departments,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'employment_status' => 'sometimes|string|in:Active,Inactive,Terminated',
            'schedule' => 'sometimes|string',
            'speciality' => 'sometimes|string|max:255',
            'salary' => 'sometimes|numeric',
            'updated_at' => 'nullable|date',
        ]);

        $employee->update($data);
        return response()->json($employee);
    }

    /**
     * Remove the specified employee.
     */
    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}




