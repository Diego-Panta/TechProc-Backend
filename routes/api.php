<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
