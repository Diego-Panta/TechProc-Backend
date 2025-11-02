<?php

use App\Domains\Users\Controllers\UserController;
use App\Domains\Users\Controllers\RoleController;
use App\Domains\Users\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

// All routes require authentication with Sanctum
Route::middleware(['auth:sanctum'])->group(function () {

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);

        // User roles and permissions
        Route::post('/{id}/roles', [UserController::class, 'assignRoles']);
        Route::post('/{id}/permissions', [UserController::class, 'assignPermissions']);
    });

    // Role management routes
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);

        // Role permissions
        Route::post('/{id}/permissions', [RoleController::class, 'assignPermissions']);
    });

    // Permission management routes
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::get('/{id}', [PermissionController::class, 'show']);
        Route::put('/{id}', [PermissionController::class, 'update']);
        Route::delete('/{id}', [PermissionController::class, 'destroy']);
    });
});
