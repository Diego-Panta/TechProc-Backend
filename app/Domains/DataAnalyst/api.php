<?php

use App\Domains\DataAnalyst\Http\Controllers\DashboardApiController;
use App\Domains\DataAnalyst\Http\Controllers\ExportReportApiController;
use App\Domains\DataAnalyst\Http\Controllers\RiskPredictionController;
use App\Domains\DataAnalyst\Http\Controllers\AnalyticsDashboardController;
use App\Domains\DataAnalyst\Middleware\DataAnalystMiddleware;
use App\Domains\DataAnalyst\Http\Controllers\AttendanceAnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\ProgressAnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\PerformanceAnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\AnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\DropoutPredictionController;
use Illuminate\Support\Facades\Route;

// API Routes for DataAnalyst module
Route::prefix('data-analyst')->name('api.data-analyst.')->group(function () {

    Route::get('/attendance', [AnalyticsController::class, 'getAttendanceMetrics']);
    Route::get('/attendance/simple', [AnalyticsController::class, 'getSimpleAttendance']);
    Route::get('/progress', [AnalyticsController::class, 'getProgressMetrics']);
    Route::get('/performance', [AnalyticsController::class, 'getPerformanceMetrics']);

    Route::get('/students/active', [AnalyticsController::class, 'getActiveStudents']);

    /*Route::prefix('dropout-prediction')->group(function () {
        Route::get('/predictions', [DropoutPredictionController::class, 'getPredictions']);
        Route::get('/model/metrics', [DropoutPredictionController::class, 'getModelMetrics']);
        Route::get('/training-dataset', [DropoutPredictionController::class, 'getTrainingDataset']);
        Route::post('/train-model', [DropoutPredictionController::class, 'trainModel']);
    });*/

    // Rutas de predicción de riesgo
    /*Route::prefix('risk-prediction')->name('risk-prediction.')->group(function () {
        Route::get('student/{enrollmentId}', [RiskPredictionController::class, 'getStudentRisk'])
            ->name('student');
        Route::get('students', [RiskPredictionController::class, 'getAtRiskStudents'])
            ->name('students');
        Route::post('calculate-all', [RiskPredictionController::class, 'calculateAllRisks'])
            ->name('calculate-all');
        Route::get('statistics', [RiskPredictionController::class, 'getRiskStatistics'])
            ->name('statistics');
    });

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('student/{enrollmentId}', [AttendanceAnalyticsController::class, 'getStudentAttendance'])
            ->name('student');
        Route::get('issues', [AttendanceAnalyticsController::class, 'getAttendanceIssues'])
            ->name('issues');
        Route::get('group/{groupId}', [AttendanceAnalyticsController::class, 'getGroupAttendance'])
            ->name('group');
        Route::get('statistics', [AttendanceAnalyticsController::class, 'getAttendanceStatistics'])
            ->name('statistics');
        Route::post('calculate-all', [AttendanceAnalyticsController::class, 'calculateAllAttendance'])
            ->name('calculate-all');
    });

    Route::prefix('progress')->name('progress.')->group(function () {
        Route::get('student/{enrollmentId}', [ProgressAnalyticsController::class, 'getStudentProgress'])
            ->name('student');
        Route::get('issues', [ProgressAnalyticsController::class, 'getProgressIssues'])
            ->name('issues');
    });

    Route::prefix('performance')->name('performance.')->group(function () {
        // Análisis principal
        Route::get('group/{groupId}', [PerformanceAnalyticsController::class, 'getGroupPerformance'])
            ->name('group');
        Route::get('issues', [PerformanceAnalyticsController::class, 'getPerformanceIssues'])
            ->name('issues');
        Route::get('group/{groupId}/grade-distribution', [PerformanceAnalyticsController::class, 'getGradeDistribution'])
            ->name('grade-distribution');
        Route::get('group/{groupId}/instructor-effectiveness', [PerformanceAnalyticsController::class, 'getInstructorEffectiveness'])
            ->name('instructor-effectiveness');
        Route::get('group/{groupId}/module-performance', [PerformanceAnalyticsController::class, 'getModulePerformance'])
            ->name('module-performance');
    });

    // Rutas del dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('overview', [AnalyticsDashboardController::class, 'getOverview'])
            ->name('overview');
        Route::get('risk-trends', [AnalyticsDashboardController::class, 'getRiskTrends'])
            ->name('risk-trends');
        Route::get('component-analysis', [AnalyticsDashboardController::class, 'getComponentAnalysis'])
            ->name('component-analysis');
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
        // Descargar reporte (ruta pública con token)
        Route::get('/download/{token}', [ExportReportApiController::class, 'downloadReport'])->name('download');
        Route::middleware([DataAnalystMiddleware::class])->group(function () {
            // Generar y guardar reporte
            Route::post('/generate', [ExportReportApiController::class, 'generateReport'])->name('generate');
            // Listar reportes del usuario
            Route::get('/reports', [ExportReportApiController::class, 'listReports'])->name('reports.list');
            // Obtener estadísticas
            Route::get('/reports/stats', [ExportReportApiController::class, 'getStats'])->name('reports.stats');
            // Eliminar reporte
            Route::delete('/reports/{token}', [ExportReportApiController::class, 'deleteReport'])->name('reports.delete');
            // Rutas existentes
            Route::get('/filter-options/{reportType}', [ExportReportApiController::class, 'getFilterOptions'])->name('filter-options');
            Route::get('/report-types', [ExportReportApiController::class, 'getReportTypes'])->name('report-types');
            Route::post('/preview', [ExportReportApiController::class, 'previewReport'])->name('preview');
        });
    });*/
});
