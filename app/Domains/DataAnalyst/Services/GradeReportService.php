<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\GradeReportRepository;

class GradeReportService
{
    protected $gradeReportRepository;

    public function __construct(GradeReportRepository $gradeReportRepository)
    {
        $this->gradeReportRepository = $gradeReportRepository;
    }

    public function getGradeReport(array $filters = [])
    {
        return $this->gradeReportRepository->getGradeReport($filters);
    }

    public function getGradeStatistics(array $filters = [])
    {
        return $this->gradeReportRepository->getGradeStatistics($filters);
    }

    public function getTopPerformers(array $filters = [])
    {
        $limit = $filters['limit'] ?? 10;
        return $this->gradeReportRepository->getTopPerformers($filters, $limit);
    }

    public function getFilterData(array $filters = [])
    {
        return [
            'courses' => $this->gradeReportRepository->getAvailableCourses(),
            'academicPeriods' => $this->gradeReportRepository->getAvailableAcademicPeriods()
        ];
    }
}