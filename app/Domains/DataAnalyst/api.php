<?php

use App\Domains\DataAnalyst\Http\Controllers\AnalyticsController;
use App\Domains\DataAnalyst\Http\Controllers\DropoutPredictionController;
use App\Domains\DataAnalyst\Http\Controllers\DropoutDatasetSyncController;
use App\Domains\DataAnalyst\Http\Controllers\BigQuerySyncController;
use App\Domains\DataAnalyst\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// API Routes for DataAnalyst module
Route::prefix('data-analyst')->name('api.data-analyst.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'getDashboard']);
    Route::get('/dashboard/charts', [DashboardController::class, 'getCharts']);

    // Nuevas rutas para grupos
    Route::get('/groups', [AnalyticsController::class, 'getGroupsList']);
    Route::get('/groups/active', [AnalyticsController::class, 'getActiveGroups']);


    // Módulo Asistencia
    Route::get('/attendance', [AnalyticsController::class, 'getAttendanceMetrics']);
    Route::get('/charts/attendance-status', [AnalyticsController::class, 'getAttendanceStatusDistribution']);
    Route::get('/charts/weekly-absence-trends', [AnalyticsController::class, 'getWeeklyAbsenceTrends']);
    Route::get('/charts/attendance-calendar', [AnalyticsController::class, 'getAttendanceCalendar']);

    // Módulo Rendimiento
    Route::get('/performance', [AnalyticsController::class, 'getPerformanceMetrics']);
    Route::get('/charts/grade-distribution', [AnalyticsController::class, 'getGradeDistribution']);
    Route::get('/charts/attendance-grade-correlation', [AnalyticsController::class, 'getAttendanceGradeCorrelation']);
    Route::get('/charts/group-performance-radar', [AnalyticsController::class, 'getGroupPerformanceRadar']);

    // Nuevas gráficas VIABLES - Módulo Progreso
    Route::get('/progress', [AnalyticsController::class, 'getProgressMetrics']);
    Route::get('/charts/grade-evolution', [AnalyticsController::class, 'getGradeEvolution']);

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

    // Sincronización del dataset
    Route::prefix('dataset-sync')->group(function () {
        Route::post('/sync', [DropoutDatasetSyncController::class, 'syncDataset']);
        Route::get('/status', [DropoutDatasetSyncController::class, 'getSyncStatus']);
    });

    // Sincronización completa
    Route::post('/bigquery/sync-full', [BigQuerySyncController::class, 'syncFull']);
    // Sincronización incremental (solo nuevos datos)
    Route::post('/bigquery/sync-incremental', [BigQuerySyncController::class, 'syncIncremental']);
    // Vaciar tablas en BigQuery
    Route::post('/bigquery/truncate', [BigQuerySyncController::class, 'truncateTables']);
    // Obtener estado de sincronización
    Route::get('/bigquery/status', [BigQuerySyncController::class, 'getSyncStatus']);
});
