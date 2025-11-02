<?php

use App\Domains\Users\Controllers\UserController;
use App\Domains\Users\Controllers\RoleController;
use App\Domains\Users\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

// All routes require authentication with Sanctum
Route::middleware(['auth:sanctum'])->group(function () {

    // Ruta de prueba SIN permisos (para verificar autenticación)
    Route::get('/test-auth', function (\Illuminate\Http\Request $request) {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado correctamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'has_super_admin_role' => $user->hasRole('super_admin'),
                'has_users_view_permission' => $user->hasPermissionTo('users.view'),
            ]
        ]);
    });

    // ========================================
    // GESTIÓN DE USUARIOS
    // ========================================

    // Ver usuarios (requiere users.view)
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
    });

    // Ver detalles de usuarios (requiere users.view-any)
    Route::middleware(['permission:users.view-any'])->group(function () {
        Route::get('/users/{id}', [UserController::class, 'show']);
    });

    // Crear usuarios (requiere users.create)
    Route::middleware(['permission:users.create'])->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });

    // Actualizar usuarios (requiere users.update)
    Route::middleware(['permission:users.update'])->group(function () {
        Route::put('/users/{id}', [UserController::class, 'update']);
    });

    // Eliminar usuarios (requiere users.delete)
    Route::middleware(['permission:users.delete'])->group(function () {
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

    // Asignar roles a usuarios (requiere users.assign-roles)
    Route::middleware(['permission:users.assign-roles'])->group(function () {
        Route::post('/users/{id}/roles', [UserController::class, 'assignRoles']);
    });

    // Asignar permisos a usuarios (requiere users.assign-permissions)
    Route::middleware(['permission:users.assign-permissions'])->group(function () {
        Route::post('/users/{id}/permissions', [UserController::class, 'assignPermissions']);
    });

    // ========================================
    // GESTIÓN DE ROLES
    // ========================================

    // Ver roles (requiere roles.view)
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
    });

    // Ver detalles de roles (requiere roles.view-any)
    Route::middleware(['permission:roles.view-any'])->group(function () {
        Route::get('/roles/{id}', [RoleController::class, 'show']);
    });

    // Crear roles (requiere roles.create)
    Route::middleware(['permission:roles.create'])->group(function () {
        Route::post('/roles', [RoleController::class, 'store']);
    });

    // Actualizar roles (requiere roles.update)
    Route::middleware(['permission:roles.update'])->group(function () {
        Route::put('/roles/{id}', [RoleController::class, 'update']);
    });

    // Eliminar roles (requiere roles.delete)
    Route::middleware(['permission:roles.delete'])->group(function () {
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    });

    // Asignar permisos a roles (requiere roles.assign-permissions)
    Route::middleware(['permission:roles.assign-permissions'])->group(function () {
        Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);
    });

    // ========================================
    // GESTIÓN DE PERMISOS
    // ========================================

    // Ver permisos (requiere permissions.view)
    Route::middleware(['permission:permissions.view'])->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
    });

    // Ver detalles de permisos (requiere permissions.view-any)
    Route::middleware(['permission:permissions.view-any'])->group(function () {
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    });

    // Crear permisos (requiere permissions.create)
    Route::middleware(['permission:permissions.create'])->group(function () {
        Route::post('/permissions', [PermissionController::class, 'store']);
    });

    // Actualizar permisos (requiere permissions.update)
    Route::middleware(['permission:permissions.update'])->group(function () {
        Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    });

    // Eliminar permisos (requiere permissions.delete)
    Route::middleware(['permission:permissions.delete'])->group(function () {
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    });
});
