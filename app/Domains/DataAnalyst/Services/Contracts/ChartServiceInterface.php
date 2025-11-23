<?php
// app/Domains/DataAnalyst/Services/Contracts/ChartServiceInterface.php

namespace App\Domains\DataAnalyst\Services\Contracts;

interface ChartServiceInterface
{
    // Asistencia
    public function getAttendanceStatusDistribution(array $filters = []): array;
    public function getWeeklyAbsenceTrends(array $filters = []): array;
    public function getAttendanceCalendar(array $filters = []): array;
    
    // Rendimiento
    public function getGradeDistribution(array $filters = []): array;
    public function getAttendanceGradeCorrelation(array $filters = []): array;
    public function getGroupPerformanceRadar(array $filters = []): array;
    
    // Progreso
    public function getGradeEvolution(array $filters = []): array;
}