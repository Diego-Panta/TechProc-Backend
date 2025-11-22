<?php

use App\Domains\AuthenticationSessions\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/2fa/verify-login', [AuthController::class, 'verify2FALogin']);
    
    // Protected routes (usando Sanctum)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);

        // Secondary email routes (para notificaciones, recuperación, etc.)
        Route::post('/secondary-email/add', [AuthController::class, 'addSecondaryEmail']);
        Route::post('/secondary-email/verify', [AuthController::class, 'verifySecondaryEmail']);
        Route::post('/secondary-email/resend-code', [AuthController::class, 'resendSecondaryEmailCode']);
        Route::delete('/secondary-email/remove', [AuthController::class, 'removeSecondaryEmail']);

        // 2FA routes
        Route::post('/2fa/enable', [AuthController::class, 'enable2FA']);
        Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);
        Route::post('/2fa/disable', [AuthController::class, 'disable2FA']);
        Route::post('/2fa/recovery-codes/regenerate', [AuthController::class, 'regenerateRecoveryCodes']);
    });
});