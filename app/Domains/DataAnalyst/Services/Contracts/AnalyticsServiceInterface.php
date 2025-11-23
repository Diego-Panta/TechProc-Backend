<?php
// app/Domains/DataAnalyst/Services/Contracts/AnalyticsServiceInterface.php

namespace App\Domains\DataAnalyst\Services\Contracts;

interface AnalyticsServiceInterface
{
    public function getAttendanceMetrics(array $filters = []): array;
    public function getProgressMetrics(array $filters = []): array;
    public function getPerformanceMetrics(array $filters = []): array;
}