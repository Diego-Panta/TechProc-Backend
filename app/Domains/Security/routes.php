<?php

use App\Domains\Security\Http\Controllers\SecurityDashboardController;
use App\Domains\Security\Http\Controllers\SecurityEventController;
use App\Domains\Security\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security API Routes
|--------------------------------------------------------------------------
|
| Rutas del módulo de seguridad
| Todas requieren autenticación con Sanctum
|
| NOTA: En Sanctum API, "sesiones" = "tokens", por lo que usamos solo
| SessionController para gestionar ambos conceptos.
|
*/

Route::prefix('security')->middleware(['auth:sanctum'])->group(function () {

    // Dashboard de seguridad
    Route::get('dashboard', [SecurityDashboardController::class, 'index']);

    // Gestión de sesiones (tokens Sanctum)
    Route::prefix('sessions')->group(function () {
        Route::get('/', [SessionController::class, 'index']); // Mis sesiones o de un usuario (si rol security + ?user_id=X)
        Route::get('/all', [SessionController::class, 'all']); // TODAS las sesiones activas (solo rol security)
        Route::get('/suspicious', [SessionController::class, 'suspicious']); // Sesiones sospechosas (propias o de todos)
        Route::delete('/{sessionId}', [SessionController::class, 'destroy']); // Terminar sesión específica (revocar token)
        Route::post('/terminate-all', [SessionController::class, 'terminateAll']); // Terminar todas las sesiones. Soporta ?user_id=X para rol security
    });

    // Eventos de seguridad
    Route::prefix('events')->group(function () {
        Route::get('/', [SecurityEventController::class, 'index']); // Mis eventos o de un usuario (si rol security + ?user_id=X)
        Route::get('/all', [SecurityEventController::class, 'all']); // TODOS los eventos (solo rol security)
        Route::get('/recent', [SecurityEventController::class, 'recent']);
        Route::get('/critical', [SecurityEventController::class, 'critical']);
        Route::get('/statistics', [SecurityEventController::class, 'statistics']);
    });
});
