<?php
// app/Domains/DataAnalyst/Services/BigQueryAnalyticsService.php

namespace App\Domains\DataAnalyst\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use App\Domains\DataAnalyst\Services\Contracts\AnalyticsServiceInterface;
use App\Domains\DataAnalyst\Services\Contracts\ChartServiceInterface;
use App\Domains\DataAnalyst\Services\Charts\AttendanceChartService;
use App\Domains\DataAnalyst\Services\Charts\PerformanceChartService;
use App\Domains\DataAnalyst\Services\Charts\ProgressChartService;
use App\Domains\DataAnalyst\Services\Traits\QueryBuilderTrait;
use App\Domains\DataAnalyst\Services\Traits\CacheManagerTrait;
use App\Domains\DataAnalyst\Services\Traits\DataFormatterTrait;

class BigQueryAnalyticsService implements AnalyticsServiceInterface, ChartServiceInterface
{
    use QueryBuilderTrait, CacheManagerTrait, DataFormatterTrait;

    protected $bigQuery;
    protected $dataset;
    
    // Servicios especializados
    protected $attendanceChartService;
    protected $performanceChartService;
    protected $progressChartService;
    protected $groupService;
    protected $progressDataService;
    protected $attendanceDataService;
    protected $performanceDataService;
    
    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('BIGQUERY_PROJECT_ID'),
            'keyFilePath' => base_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);

        $this->dataset = $this->bigQuery->dataset('lms_analytics');
        
        // Inicializar servicios especializados
        $this->initializeServices();
    }

    /**
     * Inicializa servicios especializados
     */
    private function initializeServices(): void
    {
        $this->attendanceChartService = new AttendanceChartService($this->bigQuery);
        $this->performanceChartService = new PerformanceChartService($this->bigQuery);
        $this->progressChartService = new ProgressChartService($this->bigQuery);
        $this->groupService = new GroupService();
        $this->progressDataService = new ProgressDataService();
        $this->attendanceDataService = new AttendanceDataService();
        $this->performanceDataService = new PerformanceDataService();
    }

    /**
     * Métricas de asistencia CON FILTROS COMPLETOS
     */
    public function getAttendanceMetrics(array $filters = []): array
    {
        // Usar el nuevo servicio con filtros
        return $this->attendanceDataService->getAttendanceMetricsWithFilters($filters);
    }

    /**
     * Métricas de progreso
     */
    public function getProgressMetrics(array $filters = []): array
    {
        // Usar el nuevo servicio con filtros
        return $this->progressDataService->getProgressMetricsWithFilters($filters);
    }

    /**
     * Obtiene lista de grupos para filtros
     */
    public function getGroupsList(array $filters = []): array
    {
        return $this->groupService->getGroupsList($filters);
    }

    /**
     * Obtiene grupos activos para filtros
     */
    public function getActiveGroups(): array
    {
        return $this->groupService->getActiveGroups();
    }

    /**
     * Métricas de rendimiento
     */
    public function getPerformanceMetrics(array $filters = []): array
    {
        // Usar el nuevo servicio con filtros
        return $this->performanceDataService->getPerformanceMetricsWithFilters($filters);
    }

    // Delegación de métodos de gráficas a servicios especializados
    
    public function getAttendanceStatusDistribution(array $filters = []): array
    {
        return $this->attendanceChartService->getAttendanceStatusDistribution($filters);
    }

    public function getWeeklyAbsenceTrends(array $filters = []): array
    {
        return $this->attendanceChartService->getWeeklyAbsenceTrends($filters);
    }

    public function getAttendanceCalendar(array $filters = []): array
    {
        return $this->attendanceChartService->getAttendanceCalendar($filters);
    }

    public function getGradeDistribution(array $filters = []): array
    {
        return $this->performanceChartService->getGradeDistribution($filters);
    }

    public function getAttendanceGradeCorrelation(array $filters = []): array
    {
        return $this->performanceChartService->getAttendanceGradeCorrelation($filters);
    }

    public function getGroupPerformanceRadar(array $filters = []): array
    {
        return $this->performanceChartService->getGroupPerformanceRadar($filters);
    }

    public function getGradeEvolution(array $filters = []): array
    {
        return $this->progressChartService->getGradeEvolution($filters);
    }
}