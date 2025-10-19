<?php

use App\Domains\AuthenticationSessions\Controllers\AuthController;
use App\Domains\AuthenticationSessions\Middleware\FirebaseJwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify', [AuthController::class, 'verifyToken']);
    
    // Protected routes (usando nuestro middleware Firebase JWT)
    Route::middleware([FirebaseJwtMiddleware::class])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/sessions/{user_id}', [AuthController::class, 'getSessions']);
    });
});

// Ruta protegida de ejemplo
Route::middleware([FirebaseJwtMiddleware::class])->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => [
            'user' => $request->user(),
            'roles' => $request->user()->role
        ]
    ]);
});
