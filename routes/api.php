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

// [DEV] Listar todos los usuarios (sin autenticación - solo desarrollo)
Route::get('/dev/users', function () {
    $users = \App\Models\User::select('id', 'name', 'email', 'created_at')
        ->with('roles:id,name')
        ->get();
    return response()->json([
        'success' => true,
        'count' => $users->count(),
        'data' => $users
    ]);
});

// [DEV] Listar todas las tablas de la base de datos
Route::get('/dev/tables', function () {
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    $dbName = \Illuminate\Support\Facades\DB::getDatabaseName();
    $key = "Tables_in_{$dbName}";

    $tableNames = array_map(fn($table) => $table->$key, $tables);

    return response()->json([
        'success' => true,
        'database' => $dbName,
        'count' => count($tableNames),
        'tables' => $tableNames
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
