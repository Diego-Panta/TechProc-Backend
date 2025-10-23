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
        $courses = $this->courseReportRepository->getCoursesWithFilters($filters);
        
        // Agregar datos de instructores a cada curso
        $courses->getCollection()->transform(function ($course) {
            $course->instructors_data = $this->courseReportRepository->getCourseInstructors($course->id);
            return $course;
        });

        return $courses;
    }

    public function getCourseDetail($courseId)
    {
        $course = $this->courseReportRepository->getCourseDetail($courseId);
        
        // Agregar datos de instructores al detalle del curso
        $course->instructors_data = $this->courseReportRepository->getCourseInstructors($courseId);
        
        return $course;
    }

    public function getCourseStatistics(array $filters = [])
    {
        return $this->courseReportRepository->getCourseStatistics($filters);
    }
}