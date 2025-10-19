<?php

use Illuminate\Support\Facades\Route;
use App\Domains\SupportTechnical\Http\Controllers\TicketController;
use App\Domains\SupportTechnical\Http\Controllers\TicketEscalationController;
use App\Domains\SupportTechnical\Http\Controllers\TicketTrackingController;

// TODO: Agregar middleware de autenticación cuando esté disponible
// Route::middleware(['auth:api'])->group(function () {

Route::prefix('tickets')->group(function () {
    
    // Estadísticas (debe ir antes de las rutas con parámetros)
    Route::get('/stats', [TicketController::class, 'stats']);
    
    // Escalaciones (antes de las rutas con {ticket_id})
    Route::get('/escalations', [TicketEscalationController::class, 'index']);
    Route::post('/escalations/{escalation_id}/approve', [TicketEscalationController::class, 'approve']);
    
    // Gestión de Tickets
    Route::get('/', [TicketController::class, 'index']);
    Route::get('/{ticket_id}', [TicketController::class, 'show']);
    Route::post('/', [TicketController::class, 'store']);
    Route::post('/{ticket_id}/take', [TicketController::class, 'take']);
    Route::put('/{ticket_id}/status', [TicketController::class, 'updateStatus']);
    Route::post('/{ticket_id}/resolve', [TicketController::class, 'resolve']);
    Route::post('/{ticket_id}/close', [TicketController::class, 'close']);
    
    // Escalaciones de ticket específico
    Route::post('/{ticket_id}/escalate', [TicketEscalationController::class, 'escalate']);
    
    // Seguimiento
    Route::post('/{ticket_id}/tracking', [TicketTrackingController::class, 'store']);
    
});

// });
