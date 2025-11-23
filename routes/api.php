<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toISOString(),
        'service' => 'TechProc Backend API'
    ]);
});

// Endpoint de prueba público
Route::get('/test-public', function () {
    return response()->json([
        'success' => true,
        'message' => 'Endpoint público funcionando correctamente',
        'timestamp' => now()->toISOString()
    ]);
});

// Verificar token (solo desarrollo) - Requiere autenticación
Route::middleware('auth:sanctum')->get('/check-token', function (Request $request) {
    $user = $request->user();
    $token = $request->user()->currentAccessToken();

    return response()->json([
        'success' => true,
        'message' => 'Token válido',
        'data' => [
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'tokenable_type' => $token->tokenable_type,
                'tokenable_id' => $token->tokenable_id,
                'abilities' => $token->abilities,
                'created_at' => $token->created_at,
                'last_used_at' => $token->last_used_at,
                'expires_at' => $token->expires_at,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'class' => get_class($user),
            ],
        ]
    ]);
});

//Incluir rutas del dominio Infraestructura
require app_path('Domains/SupportInfrastructure/routes.php');

// Incluir rutas del dominio AuthenticationSessions
require app_path('Domains/AuthenticationSessions/routes.php');

// Incluir rutas del dominio Users
require app_path('Domains/Users/routes.php');

// Incluir rutas del dominio Soporte Técnico
require base_path('app/Domains/SupportTechnical/routes.php');

// Incluir rutas del dominio LMS
require base_path('app/Domains/Lms/routes.php');

require base_path('app/Domains/DeveloperWeb/api.php');

require base_path('app/Domains/DataAnalyst/api.php');

// ============ CHATBOT & FAQs ============

// Rutas públicas del chatbot (sin autenticación)
Route::prefix('chatbot')->group(function () {
    // FAQs
    Route::get('faqs', [ChatbotController::class, 'indexFaqs']);
    Route::get('faqs/search', [ChatbotController::class, 'searchFaqs']);
    Route::get('faqs/most-used', [ChatbotController::class, 'mostUsedFaqs']);
    Route::get('faqs/{faq}', [ChatbotController::class, 'showFaq']);

    // Conversaciones
    Route::post('conversations', [ChatbotController::class, 'storeConversation']);
    Route::get('conversations/{conversation}', [ChatbotController::class, 'showConversation']);
    Route::put('conversations/{conversation}/resolve', [ChatbotController::class, 'resolveConversation']);
});

// Rutas protegidas (requieren autenticación - solo admin)
Route::middleware(['auth:sanctum'])->prefix('chatbot')->group(function () {
    // FAQs - Admin
    Route::post('faqs', [ChatbotController::class, 'storeFaq']);
    Route::put('faqs/{faq}', [ChatbotController::class, 'updateFaq']);
    Route::delete('faqs/{faq}', [ChatbotController::class, 'destroyFaq']);

    // Conversaciones - Admin
    Route::get('conversations', [ChatbotController::class, 'indexConversations']);
    Route::put('conversations/{conversation}/hand-to-human', [ChatbotController::class, 'handToHuman']);

    // Estadísticas - Admin
    Route::get('statistics', [ChatbotController::class, 'statistics']);
});

// Ruta protegida de ejemplo - CON MIDDLEWARE CORRECTO
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => [
            'user' => $request->user(),
            'roles' => $request->user()->role
        ]
    ]);
});
