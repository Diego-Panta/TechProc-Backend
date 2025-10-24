<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Administrator\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Aquí se definen las rutas del módulo de administración.
| Todas las rutas requieren autenticación JWT y permisos de administrador.
|
*/

Route::prefix('admin')->middleware('admin')->group(function () {
    
    // ========================================
    // GESTIÓN DE USUARIOS
    // ========================================
    
    // 2.1.1. Listar Todos los Usuarios
    Route::get('/users', [AdminController::class, 'getUsers'])
        ->name('admin.users.index');
    
    // 2.1.2. Obtener Detalles de Usuario
    Route::get('/users/{user_id}', [AdminController::class, 'getUser'])
        ->name('admin.users.show');
    
    // 2.1.3. Crear Usuario
    Route::post('/users', [AdminController::class, 'createUser'])
        ->name('admin.users.store');
    
    // 2.1.4. Actualizar Usuario
    Route::put('/users/{user_id}', [AdminController::class, 'updateUser'])
        ->name('admin.users.update');
    
    // 2.1.5. Eliminar Usuario
    Route::delete('/users/{user_id}', [AdminController::class, 'deleteUser'])
        ->name('admin.users.destroy');
    
    // ========================================
    // SOLICITUDES DE REGISTRO
    // ========================================
    
    // 2.2.1. Listar Solicitudes Pendientes
    Route::get('/registration-requests', [AdminController::class, 'getRegistrationRequests'])
        ->name('admin.registration-requests.index');
    
    // 2.2.2. Aprobar Solicitud de Registro
    Route::post('/registration-requests/{request_id}/approve', [AdminController::class, 'approveRegistrationRequest'])
        ->name('admin.registration-requests.approve');
    
    // 2.2.3. Rechazar Solicitud de Registro
    Route::post('/registration-requests/{request_id}/reject', [AdminController::class, 'rejectRegistrationRequest'])
        ->name('admin.registration-requests.reject');
    
    // ========================================
    // GESTIÓN DE DEPARTAMENTOS
    // ========================================
    
    // Listar Departamentos
    Route::get('/departments', [AdminController::class, 'getDepartments'])
        ->name('admin.departments.index');
    
    // Crear Departamento
    Route::post('/departments', [AdminController::class, 'createDepartment'])
        ->name('admin.departments.store');
    
    // Obtener Departamento
    Route::get('/departments/{department_id}', [AdminController::class, 'getDepartment'])
        ->name('admin.departments.show');
    
    // Actualizar Departamento
    Route::put('/departments/{department_id}', [AdminController::class, 'updateDepartment'])
        ->name('admin.departments.update');
    
    // Eliminar Departamento
    Route::delete('/departments/{department_id}', [AdminController::class, 'deleteDepartment'])
        ->name('admin.departments.destroy');
    
    // ========================================
    // GESTIÓN DE POSICIONES
    // ========================================
    
    // Listar Posiciones
    Route::get('/positions', [AdminController::class, 'getPositions'])
        ->name('admin.positions.index');
    
    // Crear Posición
    Route::post('/positions', [AdminController::class, 'createPosition'])
        ->name('admin.positions.store');
    
    // Obtener Posición
    Route::get('/positions/{position_id}', [AdminController::class, 'getPosition'])
        ->name('admin.positions.show');
    
    // Actualizar Posición
    Route::put('/positions/{position_id}', [AdminController::class, 'updatePosition'])
        ->name('admin.positions.update');
    
    // Eliminar Posición
    Route::delete('/positions/{position_id}', [AdminController::class, 'deletePosition'])
        ->name('admin.positions.destroy');
    
    // ========================================
    // GESTIÓN DE EMPLEADOS
    // ========================================
    
    // Listar Empleados
    Route::get('/employees', [AdminController::class, 'getEmployees'])
        ->name('admin.employees.index');
    
    // Crear Empleado
    Route::post('/employees', [AdminController::class, 'createEmployee'])
        ->name('admin.employees.store');
    
    // Obtener Empleado
    Route::get('/employees/{employee_id}', [AdminController::class, 'getEmployee'])
        ->name('admin.employees.show');
    
    // Actualizar Empleado
    Route::put('/employees/{employee_id}', [AdminController::class, 'updateEmployee'])
        ->name('admin.employees.update');
    
    // Eliminar Empleado
    Route::delete('/employees/{employee_id}', [AdminController::class, 'deleteEmployee'])
        ->name('admin.employees.destroy');
    
    // ========================================
    // DASHBOARD Y ESTADÍSTICAS
    // ========================================
    
    // Dashboard Principal
    Route::get('/dashboard', [AdminController::class, 'getDashboard'])
        ->name('admin.dashboard');
    
    // Estadísticas de Usuarios
    Route::get('/stats/users', [AdminController::class, 'getUserStats'])
        ->name('admin.stats.users');
    
    // Estadísticas de Empleados
    Route::get('/stats/employees', [AdminController::class, 'getEmployeeStats'])
        ->name('admin.stats.employees');
    
    // Estadísticas de Departamentos
    Route::get('/stats/departments', [AdminController::class, 'getDepartmentStats'])
        ->name('admin.stats.departments');
    
    // ========================================
    // EXPORTACIÓN DE DATOS
    // ========================================
    
    // Exportar Usuarios
    Route::post('/export/users', [AdminController::class, 'exportUsers'])
        ->name('admin.export.users');
    
    // Exportar Empleados
    Route::post('/export/employees', [AdminController::class, 'exportEmployees'])
        ->name('admin.export.employees');
    
    // ========================================
    // AUDITORÍA Y LOGS
    // ========================================
    
    // Logs de Auditoría de Usuario
    Route::get('/audit/users/{user_id}', [AdminController::class, 'getUserAuditLogs'])
        ->name('admin.audit.users');
    
    // Logs del Sistema
    Route::get('/logs/system', [AdminController::class, 'getSystemLogs'])
        ->name('admin.logs.system');
    
    // ========================================
    // CONFIGURACIÓN DEL SISTEMA
    // ========================================
    
    // Configuraciones Generales
    Route::get('/settings', [AdminController::class, 'getSettings'])
        ->name('admin.settings.index');
    
    // Actualizar Configuraciones
    Route::put('/settings', [AdminController::class, 'updateSettings'])
        ->name('admin.settings.update');
    
    // ========================================
    // GESTIÓN DE ROLES Y PERMISOS
    // ========================================
    
    // Listar Roles
    Route::get('/roles', [AdminController::class, 'getRoles'])
        ->name('admin.roles.index');
    
    // Asignar Rol a Usuario
    Route::post('/users/{user_id}/roles', [AdminController::class, 'assignRole'])
        ->name('admin.users.roles.assign');
    
    // Remover Rol de Usuario
    Route::delete('/users/{user_id}/roles/{role}', [AdminController::class, 'removeRole'])
        ->name('admin.users.roles.remove');
    
    // ========================================
    // GESTIÓN DE SESIONES
    // ========================================
    
    // Listar Sesiones Activas
    Route::get('/sessions', [AdminController::class, 'getActiveSessions'])
        ->name('admin.sessions.index');
    
    // Terminar Sesión
    Route::post('/sessions/{session_id}/terminate', [AdminController::class, 'terminateSession'])
        ->name('admin.sessions.terminate');
    
    // Bloquear Sesión
    Route::post('/sessions/{session_id}/block', [AdminController::class, 'blockSession'])
        ->name('admin.sessions.block');
    
    // ========================================
    // NOTIFICACIONES
    // ========================================
    
    // Enviar Notificación a Usuario
    Route::post('/notifications/send', [AdminController::class, 'sendNotification'])
        ->name('admin.notifications.send');
    
    // Listar Notificaciones
    Route::get('/notifications', [AdminController::class, 'getNotifications'])
        ->name('admin.notifications.index');
    
    // Marcar Notificación como Leída
    Route::put('/notifications/{notification_id}/read', [AdminController::class, 'markNotificationAsRead'])
        ->name('admin.notifications.read');
    
});
