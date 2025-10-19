<?php

namespace App\Domains\DataAnalyst\Services;

use App\Domains\DataAnalyst\Repositories\CourseReportRepository;

class CourseReportService
{
    protected $courseReportRepository;

    public function __construct(CourseReportRepository $courseReportRepository)
    {
        $this->courseReportRepository = $courseReportRepository;
    }

    public function getCourseReport(array $filters = [])
    {
        return $this->courseReportRepository->getCoursesWithFilters($filters);
    }

    public function getCourseDetail($courseId)
    {
        return $this->courseReportRepository->getCourseDetail($courseId);
    }

    public function getCourseStatistics(array $filters = [])
    {
        return $this->courseReportRepository->getCourseStatistics($filters);
    }
}