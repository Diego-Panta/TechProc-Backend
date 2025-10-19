<?php

use App\Domains\DataAnalyst\Http\Controllers\Api\StudentReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\CourseReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\AttendanceReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\GradeReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\FinancialReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\TicketReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\SecurityReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\DashboardApiController;
use App\Domains\DataAnalyst\Http\Controllers\Api\ExportReportApiController;

use App\Domains\DataAnalyst\Middleware\DataAnalystMiddleware;

use Illuminate\Support\Facades\Route;

// API Routes for DataAnalyst module
Route::prefix('api/data-analyst')->name('api.data-analyst.')->group(function () {

    // Student Reports API
    Route::prefix('students')->name('students.')->group(function () {
        // Public endpoints (si se necesitan en el futuro)
        // Route::get('/public', [StudentReportApiController::class, 'publicIndex'])->name('public.index');

        // Protected endpoints (requieren autenticación y rol data analyst)
        Route::middleware([DataAnalystMiddleware::class])->group(function () {
            // Listado de estudiantes con filtros
            Route::get('/', [StudentReportApiController::class, 'index'])->name('index');

            // Detalle de estudiante específico
            Route::get('/{studentId}', [StudentReportApiController::class, 'show'])->name('show');

            // Estadísticas de estudiantes
            Route::get('/stats/summary', [StudentReportApiController::class, 'getStatistics'])->name('statistics');

            // Reporte avanzado con múltiples métricas
            Route::get('/reports/advanced', [StudentReportApiController::class, 'getAdvancedReport'])->name('advanced-report');

            // Exportación de datos (futura implementación)
            // Route::post('/export', [StudentReportApiController::class, 'export'])->name('export');
        });
    });

    // Course Reports API
    Route::prefix('courses')->name('courses.')->group(function () {
        // Protected endpoints
        Route::middleware([DataAnalystMiddleware::class])->group(function () {
            // Listado de cursos con filtros
            Route::get('/', [CourseReportApiController::class, 'index'])->name('index');

            // Detalle de curso específico
            Route::get('/{courseId}', [CourseReportApiController::class, 'show'])->name('show');

            // Estadísticas de cursos
            Route::get('/stats/summary', [CourseReportApiController::class, 'getStatistics'])->name('statistics');

            // Exportación de datos (futura implementación)
            // Route::post('/export', [CourseReportApiController::class, 'export'])->name('export');
        });
    });

    // Attendance Reports API
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Protected endpoints
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Listado de registros de asistencia con filtros
            Route::get('/', [AttendanceReportApiController::class, 'index'])->name('index');

            // Estadísticas de asistencia
            Route::get('/stats/summary', [AttendanceReportApiController::class, 'getStatistics'])->name('statistics');

            // Tendencia de asistencia por fecha
            Route::get('/trend', [AttendanceReportApiController::class, 'getTrend'])->name('trend');

            // Opciones para los filtros
            Route::get('/filters/options', [AttendanceReportApiController::class, 'getFilterOptions'])->name('filter-options');

            // Exportación de datos (futura implementación)
            // Route::post('/export', [AttendanceReportApiController::class, 'export'])->name('export');
        });
    });

    // Grade Reports API
    Route::prefix('grades')->name('grades.')->group(function () {
        // Protected endpoints (descomentar cuando tengas autenticación)
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Listado de calificaciones con filtros
            Route::get('/', [GradeReportApiController::class, 'index'])->name('index');

            // Estadísticas generales de calificaciones
            Route::get('/stats/summary', [GradeReportApiController::class, 'getStatistics'])->name('statistics');

            // Estudiantes con mejor rendimiento
            Route::get('/top-performers', [GradeReportApiController::class, 'getTopPerformers'])->name('top-performers');

            // Opciones de filtro disponibles
            Route::get('/filter-options', [GradeReportApiController::class, 'getFilterOptions'])->name('filter-options');
        });
    });

    // Financial Reports API
    Route::prefix('financial')->name('financial.')->group(function () {
        // Protected endpoints
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Estadísticas financieras completas
            Route::get('/statistics', [FinancialReportApiController::class, 'getStatistics'])->name('statistics');

            // Tendencia de ingresos
            Route::get('/revenue-trend', [FinancialReportApiController::class, 'getRevenueTrend'])->name('revenue-trend');

            // Fuentes de ingresos disponibles
            Route::get('/revenue-sources', [FinancialReportApiController::class, 'getRevenueSources'])->name('revenue-sources');

            // Pagos pendientes con detalles
            Route::get('/pending-payments', [FinancialReportApiController::class, 'getPendingPayments'])->name('pending-payments');
        });
    });

    // Tickets Reports API
    Route::prefix('tickets')->name('tickets.')->group(function () {
        // Protected endpoints
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Listado de tickets con filtros
            Route::get('/', [TicketReportApiController::class, 'index'])->name('index');

            // Estadísticas completas de tickets
            Route::get('/stats/summary', [TicketReportApiController::class, 'getStatistics'])->name('statistics.summary');

            // Estadísticas detalladas por categoría
            Route::get('/stats/categories', [TicketReportApiController::class, 'getCategoryStats'])->name('statistics.categories');

            // Ranking de técnicos por rendimiento
            Route::get('/stats/technicians', [TicketReportApiController::class, 'getTechnicianRanking'])->name('statistics.technicians');

            // Exportación de datos (futura implementación)
            // Route::post('/export', [TicketReportApiController::class, 'export'])->name('export');
        });
    });

    // Security Reports API
    Route::prefix('security')->name('security.')->group(function () {
        // Protected endpoints
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Análisis completo de seguridad
            Route::get('/analysis', [SecurityReportApiController::class, 'getAnalysis'])->name('analysis');

            // Listado de eventos de seguridad
            Route::get('/events', [SecurityReportApiController::class, 'getEvents'])->name('events');

            // Listado de alertas de seguridad
            Route::get('/alerts', [SecurityReportApiController::class, 'getAlerts'])->name('alerts');

            // Datos para dashboard
            Route::get('/dashboard', [SecurityReportApiController::class, 'getDashboardData'])->name('dashboard');
        });
    });

    // Dashboard API Endpoints
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Protected endpoints (descomentar cuando tengas autenticación)
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Resumen completo del dashboard
            Route::get('/summary', [DashboardApiController::class, 'getSummary'])->name('summary');

            // Métricas específicas de estudiantes
            Route::get('/metrics/students', [DashboardApiController::class, 'getStudentMetrics'])->name('metrics.students');

            // Métricas financieras
            Route::get('/metrics/financial', [DashboardApiController::class, 'getFinancialMetrics'])->name('metrics.financial');

            // Actividades recientes
            Route::get('/activities/recent', [DashboardApiController::class, 'getRecentActivities'])->name('activities.recent');

            // Futuros endpoints para métricas específicas
            // Route::get('/metrics/courses', [DashboardApiController::class, 'getCourseMetrics'])->name('metrics.courses');
            // Route::get('/metrics/performance', [DashboardApiController::class, 'getPerformanceMetrics'])->name('metrics.performance');
            // Route::get('/metrics/support', [DashboardApiController::class, 'getSupportMetrics'])->name('metrics.support');
            // Route::get('/metrics/security', [DashboardApiController::class, 'getSecurityMetrics'])->name('metrics.security');
        });
    });

    // API Routes for DataAnalyst Export module
    Route::prefix('export')->name('export.')->group(function () {

        // Protected endpoints (descomentar cuando tengas autenticación)
        Route::middleware([DataAnalystMiddleware::class])->group(function () {

            // Generar y descargar reporte
            Route::post('/generate', [ExportReportApiController::class, 'generateReport'])->name('generate');

            // Obtener opciones de filtro por tipo de reporte
            Route::get('/filter-options/{reportType}', [ExportReportApiController::class, 'getFilterOptions'])->name('filter-options');

            // Obtener tipos de reporte disponibles
            Route::get('/report-types', [ExportReportApiController::class, 'getReportTypes'])->name('report-types');

            // Vista previa de datos del reporte
            Route::post('/preview', [ExportReportApiController::class, 'previewReport'])->name('preview');
        });
    });
});
