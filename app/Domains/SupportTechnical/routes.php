<?php

use Illuminate\Support\Facades\Route;
use App\Domains\SupportTechnical\Http\Controllers\TicketController;
use App\Domains\SupportTechnical\Http\Controllers\TicketReplyController;
use App\Domains\SupportTechnical\Http\Controllers\AttachmentController;

/*
|--------------------------------------------------------------------------
| Support Technical Routes
|--------------------------------------------------------------------------
|
| Rutas del módulo de soporte técnico
| Requieren autenticación con sanctum
|
*/

Route::middleware(['auth:sanctum'])->prefix('support')->group(function () {

    // Statistics (must be before dynamic routes)
    Route::get('/statistics', [TicketController::class, 'statistics']);

    // My Tickets (tickets del usuario autenticado)
    Route::get('/my-tickets', [TicketController::class, 'myTickets']);

    // Tickets (admin/support ven todos, otros usuarios solo los suyos)
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::post('/tickets/{id}/close', [TicketController::class, 'close']);
    Route::post('/tickets/{id}/reopen', [TicketController::class, 'reopen']);
    
    // Ticket Replies
    Route::post('/tickets/{ticketId}/replies', [TicketReplyController::class, 'store']);
    Route::put('/tickets/{ticketId}/replies/{replyId}', [TicketReplyController::class, 'update']);
    Route::delete('/tickets/{ticketId}/replies/{replyId}', [TicketReplyController::class, 'destroy']);
    
    // Attachments
    Route::get('/attachments/{id}/download', [AttachmentController::class, 'download']);
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy']);
    
});

