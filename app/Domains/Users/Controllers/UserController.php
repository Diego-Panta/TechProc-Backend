<?php

namespace App\Domains\Users\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with pagination, search and filters
     *
     * Query Parameters:
     * - per_page: int (default: 15) - Items per page
     * - page: int (default: 1) - Current page
     * - search: string - Search by name, email, dni, fullname
     * - role: string - Filter by role name
     * - has_roles: bool - Filter users with/without roles
     * - has_2fa: bool - Filter users with/without 2FA enabled
     * - email_verified: bool - Filter users with/without verified email
     * - sort_by: string (default: 'created_at') - Sort field (name, email, created_at, id)
     * - sort_order: string (default: 'desc') - Sort direction (asc, desc)
     * - with_roles: bool (default: true) - Include roles in response
     * - with_permissions: bool (default: false) - Include permissions in response
     * - created_from: date - Filter users created from date
     * - created_to: date - Filter users created to date
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role');
        $hasRoles = $request->get('has_roles');
        $has2fa = $request->get('has_2fa');
        $emailVerified = $request->get('email_verified');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $withRoles = filter_var($request->get('with_roles', true), FILTER_VALIDATE_BOOLEAN);
        $withPermissions = filter_var($request->get('with_permissions', false), FILTER_VALIDATE_BOOLEAN);
        $createdFrom = $request->get('created_from');
        $createdTo = $request->get('created_to');

        $query = User::query();

        // Include relationships
        $relations = [];
        if ($withRoles) {
            $relations[] = 'roles';
        }
        if ($withPermissions) {
            $relations[] = 'permissions';
        }
        if (!empty($relations)) {
            $query->with($relations);
        }

        // Search by multiple fields
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('fullname', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Filter by has roles
        if ($hasRoles !== null) {
            $hasRoles = filter_var($hasRoles, FILTER_VALIDATE_BOOLEAN);
            if ($hasRoles) {
                $query->has('roles');
            } else {
                $query->doesntHave('roles');
            }
        }

        // Filter by 2FA status
        if ($has2fa !== null) {
            $has2fa = filter_var($has2fa, FILTER_VALIDATE_BOOLEAN);
            if ($has2fa) {
                $query->where('two_factor_enabled', true);
            } else {
                $query->where(function ($q) {
                    $q->where('two_factor_enabled', false)
                        ->orWhereNull('two_factor_enabled');
                });
            }
        }

        // Filter by email verified status
        if ($emailVerified !== null) {
            $emailVerified = filter_var($emailVerified, FILTER_VALIDATE_BOOLEAN);
            if ($emailVerified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by created date range
        if ($createdFrom) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        // Sorting
        $allowedSortFields = ['name', 'email', 'created_at', 'id', 'fullname'];
        if (\in_array($sortBy, $allowedSortFields, true)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'has_roles' => $hasRoles,
                'has_2fa' => $has2fa,
                'email_verified' => $emailVerified,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ], 200);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'dni' => 'nullable|string|max:8|unique:users,dni',
            'fullname' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dni' => $request->dni,
                'fullname' => $request->fullname ?? $request->name,
                'avatar' => $request->avatar,
                'phone' => $request->phone,
            ]);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            $user->load('roles', 'permissions');

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $withPermissions = filter_var($request->get('with_permissions', true), FILTER_VALIDATE_BOOLEAN);

            $relations = ['roles'];
            if ($withPermissions) {
                $relations[] = 'permissions';
            }

            $user = User::with($relations)->findOrFail($id);

            $this->authorize('view', $user);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $this->authorize('update', $user);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|nullable|string|min:8',
                'dni' => ['sometimes', 'nullable', 'string', 'max:8', Rule::unique('users')->ignore($user->id)],
                'fullname' => 'sometimes|nullable|string|max:255',
                'avatar' => 'sometimes|nullable|string|max:500',
                'phone' => 'sometimes|nullable|string|max:20',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userData = [];

            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')) $userData['email'] = $request->email;
            if ($request->has('dni')) $userData['dni'] = $request->dni;
            if ($request->has('fullname')) $userData['fullname'] = $request->fullname;
            if ($request->has('avatar')) $userData['avatar'] = $request->avatar;
            if ($request->has('phone')) $userData['phone'] = $request->phone;

            if ($request->has('password') && $request->password) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            $user->load('roles', 'permissions');

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $this->authorize('delete', $user);

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            $this->authorize('assignRoles', $user);

            $user->syncRoles($request->roles);
            $user->load('roles', 'permissions');

            return response()->json([
                'success' => true,
                'message' => 'Roles asignados exitosamente',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to user
     */
    public function assignPermissions(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            $this->authorize('assignPermissions', $user);

            $user->syncPermissions($request->permissions);
            $user->load('roles', 'permissions');

            return response()->json([
                'success' => true,
                'message' => 'Permisos asignados exitosamente',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar permisos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
