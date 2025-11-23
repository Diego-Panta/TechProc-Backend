<?php

namespace App\Domains\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions with pagination, search and filters
     *
     * Query Parameters:
     * - per_page: int (default: 15, use -1 for all) - Items per page
     * - page: int (default: 1) - Current page
     * - search: string - Search by permission name
     * - category: string - Filter by category (prefix before dot, e.g., 'users', 'roles')
     * - in_use: bool - Filter permissions assigned to roles or not
     * - sort_by: string (default: 'name') - Sort field (name, created_at, id)
     * - sort_order: string (default: 'asc') - Sort direction (asc, desc)
     * - grouped: bool (default: false) - Group permissions by category
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $category = $request->get('category');
        $inUse = $request->get('in_use');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $grouped = filter_var($request->get('grouped', false), FILTER_VALIDATE_BOOLEAN);

        $query = Permission::query();

        // Search by name
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by category (prefix before dot)
        if ($category) {
            $query->where('name', 'like', "{$category}.%");
        }

        // Filter by in use (assigned to at least one role)
        if ($inUse !== null) {
            $inUse = filter_var($inUse, FILTER_VALIDATE_BOOLEAN);
            if ($inUse) {
                $query->has('roles');
            } else {
                $query->doesntHave('roles');
            }
        }

        // Sorting
        $allowedSortFields = ['name', 'created_at', 'id'];
        if (\in_array($sortBy, $allowedSortFields, true)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        // Return grouped by category
        if ($grouped) {
            $permissions = $query->get();

            $groupedPermissions = $permissions->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'other';
            })->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'count' => $group->count(),
                    'permissions' => $group->values(),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $groupedPermissions,
                'total' => $permissions->count(),
                'filters' => [
                    'search' => $search,
                    'category' => $category,
                    'in_use' => $inUse,
                    'grouped' => true,
                ],
            ], 200);
        }

        // Paginate or get all
        if ($perPage == -1) {
            $permissions = $query->get();
            return response()->json([
                'success' => true,
                'data' => $permissions,
                'total' => $permissions->count(),
                'filters' => [
                    'search' => $search,
                    'category' => $category,
                    'in_use' => $inUse,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
            ], 200);
        }

        $permissions = $query->paginate($perPage);

        // Get available categories for filters
        $categories = Permission::all()
            ->pluck('name')
            ->map(fn($name) => explode('.', $name)[0] ?? 'other')
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $permissions,
            'available_categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'in_use' => $inUse,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ], 200);
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Permission::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $permission = Permission::create(['name' => $request->name]);

            return response()->json([
                'success' => true,
                'message' => 'Permiso creado exitosamente',
                'data' => $permission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission with roles that have it
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $withRoles = filter_var($request->get('with_roles', false), FILTER_VALIDATE_BOOLEAN);

            $query = Permission::query();

            if ($withRoles) {
                $query->with('roles:id,name');
            }

            $permission = $query->findOrFail($id);

            $this->authorize('view', $permission);

            return response()->json([
                'success' => true,
                'data' => $permission
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);

            $this->authorize('update', $permission);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:permissions,name,' . $permission->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permission->name = $request->name;
            $permission->save();

            return response()->json([
                'success' => true,
                'message' => 'Permiso actualizado exitosamente',
                'data' => $permission
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified permission
     */
    public function destroy($id): JsonResponse
    {
        try {
            $permission = Permission::withCount('roles')->findOrFail($id);

            $this->authorize('delete', $permission);

            // Warn if permission is in use
            if ($permission->roles_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el permiso '{$permission->name}' porque está asignado a {$permission->roles_count} rol(es)",
                ], 409);
            }

            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permiso eliminado exitosamente'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
