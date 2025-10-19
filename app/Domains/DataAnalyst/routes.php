<?php

use Illuminate\Support\Facades\Route;
use App\Domains\DataAnalyst\Http\Controllers\StudentReportController;
use App\Domains\DataAnalyst\Http\Controllers\CourseReportController;
use App\Domains\DataAnalyst\Http\Controllers\AttendanceReportController;
use App\Domains\DataAnalyst\Http\Controllers\GradeReportController;
use App\Domains\DataAnalyst\Http\Controllers\FinancialReportController;
use App\Domains\DataAnalyst\Http\Controllers\TicketReportController;
use App\Domains\DataAnalyst\Http\Controllers\SecurityReportController;
use App\Domains\DataAnalyst\Http\Controllers\DashboardController;
use App\Domains\DataAnalyst\Http\Controllers\ExportReportController;

Route::prefix('data-analyst')->name('dataanalyst.')->group(function () {

    // Dashboard General
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/data', [DashboardController::class, 'data'])->name('data');
    });

    // Reportes de Estudiantes
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentReportController::class, 'index'])->name('index');
        Route::get('/statistics', [StudentReportController::class, 'statistics'])->name('statistics');
        Route::get('/{studentId}', [StudentReportController::class, 'show'])->name('show');
    });

    // Reportes de Cursos
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [CourseReportController::class, 'index'])->name('index');
        Route::get('/statistics', [CourseReportController::class, 'statistics'])->name('statistics');
        Route::get('/{courseId}', [CourseReportController::class, 'show'])->name('show');
    });

    // Reportes de Asistencia
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceReportController::class, 'index'])->name('index');
        Route::get('/statistics', [AttendanceReportController::class, 'statistics'])->name('statistics');
        Route::get('/trend', [AttendanceReportController::class, 'trend'])->name('trend');
    });

    // Reportes de Calificaciones
    Route::prefix('grades')->name('grades.')->group(function () {
        Route::get('/', [GradeReportController::class, 'index'])->name('index');
        Route::get('/statistics', [GradeReportController::class, 'statistics'])->name('statistics');
        Route::get('/top-performers', [GradeReportController::class, 'topPerformers'])->name('top-performers');
    });

    // Reportes Financieros
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::get('/', [FinancialReportController::class, 'index'])->name('index');
        Route::get('/statistics', [FinancialReportController::class, 'statistics'])->name('statistics');
        Route::get('/revenue-trend', [FinancialReportController::class, 'revenueTrend'])->name('revenue-trend');
        Route::get('/revenue-sources', [FinancialReportController::class, 'revenueSources'])->name('revenue-sources');
    });

    // Reportes de Tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketReportController::class, 'index'])->name('index');
        Route::get('/statistics', [TicketReportController::class, 'statistics'])->name('statistics');
    });

    // Reportes de Seguridad
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityReportController::class, 'index'])->name('index');
        Route::get('/analysis', [SecurityReportController::class, 'analysis'])->name('analysis');
        Route::get('/events', [SecurityReportController::class, 'events'])->name('events');
        Route::get('/alerts', [SecurityReportController::class, 'alerts'])->name('alerts');
    });

    // Exportación de Reportes
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/', [ExportReportController::class, 'index'])->name('index');
        Route::post('/generate', [ExportReportController::class, 'export'])->name('generate');
        Route::get('/filter-options/{reportType}', [ExportReportController::class, 'getFilterOptions'])->name('filter-options');
    });
});
