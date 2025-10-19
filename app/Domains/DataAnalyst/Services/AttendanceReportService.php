<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\AttendanceReportRepository;

class AttendanceReportService
{
    protected $attendanceReportRepository;

    public function __construct(AttendanceReportRepository $attendanceReportRepository)
    {
        $this->attendanceReportRepository = $attendanceReportRepository;
    }

    public function getAttendanceReport(array $filters = [])
    {
        return $this->attendanceReportRepository->getAttendanceWithFilters($filters);
    }

    public function getAttendanceStatistics(array $filters = [])
    {
        return $this->attendanceReportRepository->getAttendanceStatistics($filters);
    }

    public function getAttendanceTrend(array $filters = [])
    {
        return $this->attendanceReportRepository->getAttendanceTrend($filters);
    }

    public function getCoursesForFilter()
    {
        return $this->attendanceReportRepository->getCoursesForFilter();
    }

    public function getStudentsForFilter()
    {
        return $this->attendanceReportRepository->getStudentsForFilter();
    }
}