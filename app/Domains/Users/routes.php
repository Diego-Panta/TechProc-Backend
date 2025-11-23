<?php

use App\Domains\Users\Controllers\UserController;
use App\Domains\Users\Controllers\RoleController;
use App\Domains\Users\Controllers\PermissionController;
use App\Domains\Users\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// All routes require authentication with Sanctum
// Authorization is handled by Policies in the controllers
Route::middleware(['auth:sanctum'])->group(function () {
    // ========================================
    // DASHBOARD DE ADMINISTRACIÓN
    // ========================================
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

    // ========================================
    // GESTIÓN DE USUARIOS
    // ========================================
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/roles', [UserController::class, 'assignRoles']);
    Route::post('/users/{id}/permissions', [UserController::class, 'assignPermissions']);

    // ========================================
    // GESTIÓN DE ROLES
    // ========================================
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);

    // ========================================
    // GESTIÓN DE PERMISOS
    // ========================================
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
});
