<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\StudentReportRepository;

class StudentReportService
{
    protected $studentReportRepository;

    public function __construct(StudentReportRepository $studentReportRepository)
    {
        $this->studentReportRepository = $studentReportRepository;
    }

    public function getStudentReport(array $filters = [])
    {
        return $this->studentReportRepository->getStudentsWithFilters($filters);
    }

    public function getStudentDetail($studentId)
    {
        return $this->studentReportRepository->getStudentDetail($studentId);
    }

    public function getStudentStatistics(array $filters = [])
    {
        return $this->studentReportRepository->getStudentStatistics($filters);
    }
}