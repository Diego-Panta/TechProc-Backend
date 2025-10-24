<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\Lms\Models\Attendance;
use App\Domains\Lms\Models\Group;
use App\Domains\Lms\Models\ClassModel;
use App\Domains\Lms\Models\GroupParticipant;
use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AttendanceReportRepository
{
    public function getAttendanceWithFilters(array $filters = [])
    {
        $query = Attendance::with([
                'class.group.course',
                'groupParticipant.user.student'
            ])
            ->select('attendances.*')
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->join('groups', 'classes.group_id', '=', 'groups.id')
            ->join('group_participants', 'attendances.group_participant_id', '=', 'group_participants.id')
            ->join('users', 'group_participants.user_id', '=', 'users.id')
            ->leftJoin('students', 'users.id', '=', 'students.user_id');

        // Aplicar filtros
        if (!empty($filters['group_id'])) {
            $query->where('classes.group_id', $filters['group_id']);
        }

        if (!empty($filters['course_id'])) {
            $query->where('groups.course_id', $filters['course_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('students.id', $filters['student_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('classes.class_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('classes.class_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['attendance_status'])) {
            $query->where('attendances.attended', $filters['attendance_status']);
        }

        return $query->orderBy('classes.class_date', 'desc')
                    ->orderBy('classes.start_time', 'desc')
                    ->paginate($filters['per_page'] ?? 15);
    }

    public function getAttendanceStatistics(array $filters = [])
    {
        // Estadísticas generales
        $baseQuery = ClassModel::query()
            ->join('groups', 'classes.group_id', '=', 'groups.id');

        // Aplicar filtros a las clases
        if (!empty($filters['group_id'])) {
            $baseQuery->where('classes.group_id', $filters['group_id']);
        }

        if (!empty($filters['course_id'])) {
            $baseQuery->where('groups.course_id', $filters['course_id']);
        }

        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('classes.class_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('classes.class_date', '<=', $filters['end_date']);
        }

        $totalClasses = $baseQuery->count();

        // Estadísticas de asistencia
        $attendanceQuery = Attendance::query()
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->join('groups', 'classes.group_id', '=', 'groups.id')
            ->join('group_participants', 'attendances.group_participant_id', '=', 'group_participants.id')
            ->join('users', 'group_participants.user_id', '=', 'users.id')
            ->leftJoin('students', 'users.id', '=', 'students.user_id');

        // Aplicar mismos filtros a la asistencia
        if (!empty($filters['group_id'])) {
            $attendanceQuery->where('classes.group_id', $filters['group_id']);
        }

        if (!empty($filters['course_id'])) {
            $attendanceQuery->where('groups.course_id', $filters['course_id']);
        }

        if (!empty($filters['student_id'])) {
            $attendanceQuery->where('students.id', $filters['student_id']);
        }

        if (!empty($filters['start_date'])) {
            $attendanceQuery->whereDate('classes.class_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $attendanceQuery->whereDate('classes.class_date', '<=', $filters['end_date']);
        }

        $totalAttendances = $attendanceQuery->count();
        $presentAttendances = (clone $attendanceQuery)->where('attendances.attended', 'YES')->count();
        $absentAttendances = (clone $attendanceQuery)->where('attendances.attended', 'NO')->count();

        $averageAttendanceRate = $totalAttendances > 0 ? ($presentAttendances / $totalAttendances) * 100 : 0;

        // Estadísticas por grupo
        $byGroup = Group::query()
            ->select([
                'groups.id as group_id',
                'groups.name as group_name',
                'courses.title as course_name',
                DB::raw('COUNT(DISTINCT classes.id) as total_classes'),
                DB::raw('COUNT(attendances.id) as total_attendances'),
                DB::raw('COUNT(CASE WHEN attendances.attended = \'YES\' THEN 1 END) as present_count'),
                DB::raw('CASE WHEN COUNT(attendances.id) > 0 THEN 
                    (COUNT(CASE WHEN attendances.attended = \'YES\' THEN 1 END) * 100.0 / COUNT(attendances.id)) 
                    ELSE 0 END as attendance_rate')
            ])
            ->leftJoin('courses', 'groups.course_id', '=', 'courses.id')
            ->leftJoin('classes', 'groups.id', '=', 'classes.group_id')
            ->leftJoin('attendances', 'classes.id', '=', 'attendances.class_id')
            ->groupBy('groups.id', 'groups.name', 'courses.title')
            ->when(!empty($filters['group_id']), function ($q) use ($filters) {
                $q->where('groups.id', $filters['group_id']);
            })
            ->when(!empty($filters['course_id']), function ($q) use ($filters) {
                $q->where('groups.course_id', $filters['course_id']);
            })
            ->when(!empty($filters['start_date']), function ($q) use ($filters) {
                $q->whereDate('classes.class_date', '>=', $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($q) use ($filters) {
                $q->whereDate('classes.class_date', '<=', $filters['end_date']);
            })
            ->get();

        return [
            'total_classes' => $totalClasses,
            'total_attendances_recorded' => $totalAttendances,
            'present_attendances' => $presentAttendances,
            'absent_attendances' => $absentAttendances,
            'average_attendance_rate' => round($averageAttendanceRate, 2),
            'by_group' => $byGroup,
        ];
    }

    public function getAttendanceTrend(array $filters = [])
    {
        $query = Attendance::query()
            ->select([
                DB::raw('DATE(classes.class_date) as date'),
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('COUNT(CASE WHEN attended = \'YES\' THEN 1 END) as present_count'),
                DB::raw('CASE WHEN COUNT(*) > 0 THEN 
                    (COUNT(CASE WHEN attended = \'YES\' THEN 1 END) * 100.0 / COUNT(*)) 
                    ELSE 0 END as attendance_rate')
            ])
            ->join('classes', 'attendances.class_id', '=', 'classes.id')
            ->join('groups', 'classes.group_id', '=', 'groups.id')
            ->join('group_participants', 'attendances.group_participant_id', '=', 'group_participants.id')
            ->join('users', 'group_participants.user_id', '=', 'users.id')
            ->leftJoin('students', 'users.id', '=', 'students.user_id')
            ->groupBy('classes.class_date')
            ->orderBy('classes.class_date');

        // Aplicar filtros
        if (!empty($filters['group_id'])) {
            $query->where('classes.group_id', $filters['group_id']);
        }

        if (!empty($filters['course_id'])) {
            $query->where('groups.course_id', $filters['course_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('students.id', $filters['student_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('classes.class_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('classes.class_date', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($item) {
            return [
                'date' => $item->date,
                'attendance_count' => (int) $item->attendance_count,
                'present_count' => (int) $item->present_count,
                'attendance_rate' => round($item->attendance_rate, 2)
            ];
        });
    }

    /**
     * Obtener lista de cursos para el filtro
     */
    public function getCoursesForFilter()
    {
        return Course::select('id', 'title')
            ->where('status', true)
            ->orderBy('title')
            ->get();
    }

    /**
     * Obtener lista de estudiantes para el filtro
     */
    public function getStudentsForFilter()
    {
        return Student::select('id', 'first_name', 'last_name', 'email')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }
}