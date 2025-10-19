<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\Lms\Models\Course;
use App\Domains\Lms\Models\Category;
use App\Domains\Lms\Models\Enrollment;
use App\Domains\Lms\Models\EnrollmentDetail;
use App\Domains\Lms\Models\CourseOffering;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CourseReportRepository
{
    public function getCoursesWithFilters(array $filters = [])
    {
        $query = Course::with(['categories', 'instructors'])
            ->select('courses.*')
            ->withCount(['courseOfferings', 'groups']);

        // Agregar conteo de matrículas mediante subquery
        $query->addSelect([
            'enrollments_count' => EnrollmentDetail::select(DB::raw('COUNT(enrollment_details.id)'))
                ->join('course_offerings', 'enrollment_details.course_offering_id', '=', 'course_offerings.id')
                ->whereColumn('course_offerings.course_id', 'courses.id')
                ->limit(1)
        ]);

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('courses.title', 'ILIKE', "%{$search}%")
                  ->orWhere('courses.name', 'ILIKE', "%{$search}%")
                  ->orWhere('courses.description', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        if (!empty($filters['level'])) {
            $query->where('courses.level', $filters['level']);
        }

        if (isset($filters['status'])) {
            $query->where('courses.status', $filters['status']);
        }

        if (isset($filters['bestseller'])) {
            $query->where('courses.bestseller', $filters['bestseller']);
        }

        if (isset($filters['featured'])) {
            $query->where('courses.featured', $filters['featured']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('courses.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('courses.created_at', '<=', $filters['end_date']);
        }

        return $query->orderBy('courses.created_at', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function getCourseDetail($courseId)
    {
        return Course::with([
            'categories',
            'instructors',
            'courseOfferings',
            'courseOfferings.enrollmentDetails',
            'courseOfferings.enrollmentDetails.enrollment',
            'groups',
            'courseContents'
        ])->findOrFail($courseId);
    }

    public function getCourseStatistics(array $filters = [])
    {
        $baseQuery = Course::query();

        // Filtros
        if (!empty($filters['category_id'])) {
            $baseQuery->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        if (!empty($filters['level'])) {
            $baseQuery->where('level', $filters['level']);
        }

        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Estadísticas básicas
        $totalCourses = $baseQuery->count();
        $activeCourses = (clone $baseQuery)->where('status', true)->count();
        $inactiveCourses = (clone $baseQuery)->where('status', false)->count();

        // Estadísticas por nivel
        $byLevel = Course::selectRaw('level, COUNT(*) as course_count')
            ->when(!empty($filters['category_id']), function ($q) use ($filters) {
                $q->whereHas('categories', function ($q) use ($filters) {
                    $q->where('categories.id', $filters['category_id']);
                });
            })
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('created_at', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('created_at', '<=', $filters['end_date']);
            })
            ->groupBy('level')
            ->get()
            ->pluck('course_count', 'level')
            ->toArray();

        // Estadísticas por categoría
        $byCategory = Category::withCount(['courses' => function ($query) use ($filters) {
                if (!empty($filters['level'])) {
                    $query->where('level', $filters['level']);
                }
                if (!empty($filters['start_date'])) {
                    $query->whereDate('courses.created_at', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $query->whereDate('courses.created_at', '<=', $filters['end_date']);
                }
            }])
            ->when(!empty($filters['category_id']), function ($q) use ($filters) {
                $q->where('id', $filters['category_id']);
            })
            ->get()
            ->map(function ($category) {
                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'course_count' => $category->courses_count
                ];
            });

        // Cursos más matriculados - Usando las relaciones correctas
        $mostEnrolled = Course::select([
                'courses.id as course_id',
                'courses.title as course_title',
                DB::raw('COUNT(enrollment_details.id) as enrollments')
            ])
            ->leftJoin('course_offerings', 'courses.id', '=', 'course_offerings.course_id')
            ->leftJoin('enrollment_details', 'course_offerings.id', '=', 'enrollment_details.course_offering_id')
            ->when(!empty($filters['category_id']), function ($q) use ($filters) {
                $q->whereHas('categories', function ($q) use ($filters) {
                    $q->where('categories.id', $filters['category_id']);
                });
            })
            ->when(!empty($filters['level']), function ($q) use ($filters) {
                $q->where('courses.level', $filters['level']);
            })
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('courses.created_at', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('courses.created_at', '<=', $filters['end_date']);
            })
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('enrollments', 'desc')
            ->limit(10)
            ->get();

        // Bestsellers (mayor ingresos)
        $bestsellers = Course::select([
                'courses.id as course_id',
                'courses.title as course_title',
                DB::raw('COALESCE(SUM(invoices.total_amount), 0) as revenue')
            ])
            ->leftJoin('course_offerings', 'courses.id', '=', 'course_offerings.course_id')
            ->leftJoin('enrollment_details', 'course_offerings.id', '=', 'enrollment_details.course_offering_id')
            ->leftJoin('enrollments', 'enrollment_details.enrollment_id', '=', 'enrollments.id')
            ->leftJoin('invoices', 'enrollments.id', '=', 'invoices.enrollment_id')
            ->where('invoices.status', 'Paid')
            ->when(!empty($filters['category_id']), function ($q) use ($filters) {
                $q->whereHas('categories', function ($q) use ($filters) {
                    $q->where('categories.id', $filters['category_id']);
                });
            })
            ->when(!empty($filters['level']), function ($q) use ($filters) {
                $q->where('courses.level', $filters['level']);
            })
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('courses.created_at', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('courses.created_at', '<=', $filters['end_date']);
            })
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_courses' => $totalCourses,
            'active_courses' => $activeCourses,
            'inactive_courses' => $inactiveCourses,
            'by_level' => [
                'basic' => $byLevel['basic'] ?? 0,
                'intermediate' => $byLevel['intermediate'] ?? 0,
                'advanced' => $byLevel['advanced'] ?? 0,
            ],
            'by_category' => $byCategory,
            'most_enrolled' => $mostEnrolled,
            'bestsellers' => $bestsellers,
        ];
    }

    /**
     * Método auxiliar para obtener el conteo de matrículas por curso
     */
    public function getEnrollmentsCountByCourse($courseId)
    {
        return CourseOffering::where('course_id', $courseId)
            ->join('enrollment_details', 'course_offerings.id', '=', 'enrollment_details.course_offering_id')
            ->count();
    }
}