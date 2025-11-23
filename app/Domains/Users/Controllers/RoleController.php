<?php

namespace App\Domains\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with pagination, search and filters
     *
     * Query Parameters:
     * - per_page: int (default: 15) - Items per page
     * - page: int (default: 1) - Current page
     * - search: string - Search by role name
     * - has_users: bool - Filter roles with/without users
     * - has_permissions: bool - Filter roles with/without permissions
     * - sort_by: string (default: 'name') - Sort field (name, created_at, users_count)
     * - sort_order: string (default: 'asc') - Sort direction (asc, desc)
     * - with_permissions: bool (default: false) - Include permissions in response
     * - with_users_count: bool (default: true) - Include users count
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $hasUsers = $request->get('has_users');
        $hasPermissions = $request->get('has_permissions');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $withPermissions = filter_var($request->get('with_permissions', false), FILTER_VALIDATE_BOOLEAN);
        $withUsersCount = filter_var($request->get('with_users_count', true), FILTER_VALIDATE_BOOLEAN);

        $query = Role::query();

        // Include permissions if requested
        if ($withPermissions) {
            $query->with('permissions');
        }

        // Search by name
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by has users (using direct query to pivot table)
        if ($hasUsers !== null) {
            $hasUsers = filter_var($hasUsers, FILTER_VALIDATE_BOOLEAN);
            if ($hasUsers) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.role_id', 'roles.id')
                        ->where('model_has_roles.model_type', User::class);
                });
            } else {
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('model_has_roles')
                        ->whereColumn('model_has_roles.role_id', 'roles.id')
                        ->where('model_has_roles.model_type', User::class);
                });
            }
        }

        // Filter by has permissions
        if ($hasPermissions !== null) {
            $hasPermissions = filter_var($hasPermissions, FILTER_VALIDATE_BOOLEAN);
            if ($hasPermissions) {
                $query->has('permissions');
            } else {
                $query->doesntHave('permissions');
            }
        }

        // Sorting
        $allowedSortFields = ['name', 'created_at', 'id'];
        if (\in_array($sortBy, $allowedSortFields, true)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $roles = $query->paginate($perPage);

        // Add users_count manually if requested
        if ($withUsersCount) {
            $roleIds = $roles->pluck('id')->toArray();
            $usersCounts = DB::table('model_has_roles')
                ->select('role_id', DB::raw('COUNT(*) as users_count'))
                ->whereIn('role_id', $roleIds)
                ->where('model_type', User::class)
                ->groupBy('role_id')
                ->pluck('users_count', 'role_id');

            $roles->getCollection()->transform(function ($role) use ($usersCounts) {
                $role->users_count = $usersCounts[$role->id] ?? 0;
                return $role;
            });
        }

        // Sort by users_count if requested (after adding the count)
        if ($sortBy === 'users_count' && $withUsersCount) {
            $sorted = $roles->getCollection()->sortBy(
                'users_count',
                SORT_REGULAR,
                $sortOrder === 'desc'
            )->values();
            $roles->setCollection($sorted);
        }

        return response()->json([
            'success' => true,
            'data' => $roles,
            'filters' => [
                'search' => $search,
                'has_users' => $hasUsers,
                'has_permissions' => $hasPermissions,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ], 200);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
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
            $role = Role::create(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => $role
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role with users count and permissions
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $withUsers = filter_var($request->get('with_users', false), FILTER_VALIDATE_BOOLEAN);

            $role = Role::with('permissions')->findOrFail($id);

            $this->authorize('view', $role);

            // Add users count manually
            $role->users_count = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();

            // Add users list if requested
            if ($withUsers) {
                $userIds = DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->where('model_type', User::class)
                    ->limit(50)
                    ->pluck('model_id');

                $role->users = User::whereIn('id', $userIds)
                    ->select('id', 'name', 'email')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $role
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $this->authorize('update', $role);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|unique:roles,name,' . $role->id,
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('name')) {
                $role->name = $request->name;
                $role->save();
            }

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            $role->load('permissions');

            // Add users count manually
            $role->users_count = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
                'data' => $role
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $this->authorize('delete', $role);

            // Get users count manually
            $usersCount = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();

            // Prevent deletion if role has users assigned
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el rol '{$role->name}' porque tiene {$usersCount} usuario(s) asignado(s)",
                ], 409);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to role
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
            $role = Role::findOrFail($id);

            $this->authorize('assignPermissions', $role);

            $role->syncPermissions($request->permissions);
            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Permisos asignados al rol exitosamente',
                'data' => $role
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
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
