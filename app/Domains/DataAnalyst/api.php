<?php

use App\Domains\DataAnalyst\Http\Controllers\AnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\DropoutPredictionController;
use App\Domains\DataAnalyst\Http\Controllers\DropoutDatasetSyncController;
use App\Domains\DataAnalyst\Http\Controllers\BigQuerySyncController;
use App\Domains\DataAnalyst\Http\Controllers\LocalAnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\LocalExportController;
use Illuminate\Support\Facades\Route;

// API Routes for DataAnalyst module
Route::prefix('data-analyst')->name('api.data-analyst.')->group(function () {

    Route::get('/attendance', [AnalyticsController::class, 'getAttendanceMetrics']);
    Route::get('/attendance/simple', [AnalyticsController::class, 'getSimpleAttendance']);
    Route::get('/progress', [AnalyticsController::class, 'getProgressMetrics']);
    Route::get('/performance', [AnalyticsController::class, 'getPerformanceMetrics']);

    // Nuevas rutas de exportación
    Route::post('/export/attendance', [AnalyticsController::class, 'exportAttendance']);
    Route::post('/export/progress', [AnalyticsController::class, 'exportProgress']);
    Route::post('/export/performance', [AnalyticsController::class, 'exportPerformance']);

    Route::prefix('dropout-prediction')->group(function () {
        Route::get('/predictions', [DropoutPredictionController::class, 'getPredictions']);
        Route::get('/predictions/detailed', [DropoutPredictionController::class, 'getDetailedPredictions']);
        Route::get('/predictions/group/{groupId}', [DropoutPredictionController::class, 'getPredictionsByGroup']);
        Route::get('/high-risk', [DropoutPredictionController::class, 'getHighRiskStudents']);
        Route::get('/system-status', [DropoutPredictionController::class, 'getSystemStatus']);

        Route::post('/export/predictions', [DropoutPredictionController::class, 'exportPredictions']);
        Route::post('/export/detailed-predictions', [DropoutPredictionController::class, 'exportDetailedPredictions']);
        Route::post('/export/high-risk', [DropoutPredictionController::class, 'exportHighRiskStudents']);
    });

    Route::prefix('local')->group(function () {
        // Estudiantes
        Route::get('/students/active', [LocalAnalyticsController::class, 'getActiveStudents']);
        // Grupos
        Route::get('/groups/with-teachers', [LocalAnalyticsController::class, 'getGroupsWithTeachers']);
        Route::get('/groups/with-students', [LocalAnalyticsController::class, 'getGroupsWithStudents']);
        // Métricas académicas
        Route::get('/attendance/summary', [LocalAnalyticsController::class, 'getAttendanceSummary']);
        Route::get('/grades/summary', [LocalAnalyticsController::class, 'getGradesSummary']);
        // Finanzas y soporte
        Route::get('/payments/summary', [LocalAnalyticsController::class, 'getPaymentsSummary']);
        Route::get('/support/tickets', [LocalAnalyticsController::class, 'getSupportTickets']);
        // Dashboard y reportes
        Route::get('/dashboard/quick', [LocalAnalyticsController::class, 'getQuickDashboard']);
        Route::get('/report/combined', [LocalAnalyticsController::class, 'getCombinedReport']);

        // Exportaciones
        Route::prefix('export')->group(function () {
            Route::post('/students/active', [LocalExportController::class, 'exportActiveStudents']);
            Route::post('/groups/with-teachers', [LocalExportController::class, 'exportGroupsWithTeachers']);
            Route::post('/groups/with-students', [LocalExportController::class, 'exportGroupsWithStudents']);
            Route::post('/attendance/summary', [LocalExportController::class, 'exportAttendanceSummary']);
            Route::post('/grades/summary', [LocalExportController::class, 'exportGradesSummary']);
            Route::post('/dashboard/quick', [LocalExportController::class, 'exportQuickDashboard']);
            Route::post('/payments/summary', [LocalExportController::class, 'exportPaymentsSummary']);
            Route::post('/support/tickets', [LocalExportController::class, 'exportSupportTickets']);
        });
    });

    // Sincronización del dataset
    Route::prefix('dataset-sync')->group(function () {
        Route::post('/sync', [DropoutDatasetSyncController::class, 'syncDataset']);
        Route::get('/status', [DropoutDatasetSyncController::class, 'getSyncStatus']);
    });

    // Sincronización completa (REEMPLAZA todos los datos)
    Route::post('/bigquery/sync-full', [BigQuerySyncController::class, 'syncFull']);
    // Sincronización incremental (solo nuevos datos)
    Route::post('/bigquery/sync-incremental', [BigQuerySyncController::class, 'syncIncremental']);
    // Vaciar tablas en BigQuery
    Route::post('/bigquery/truncate', [BigQuerySyncController::class, 'truncateTables']);
    // Obtener estado de sincronización
    Route::get('/bigquery/status', [BigQuerySyncController::class, 'getSyncStatus']);

});
