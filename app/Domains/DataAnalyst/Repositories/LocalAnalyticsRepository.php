<?php
// app/Domains/DataAnalyst/Repositories/LocalAnalyticsRepository.php

namespace App\Domains\DataAnalyst\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class LocalAnalyticsRepository
{
    /**
     * Listado de estudiantes activos con información básica
     */
    public function getActiveStudents(array $filters = []): Collection
    {
        $query = DB::table('enrollments as e')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->join('courses as c', 'cv.course_id', '=', 'c.id')
            ->leftJoin('enrollment_results as er', 'e.id', '=', 'er.enrollment_id')
            ->select([
                'u.id as student_id',
                'u.name as student_name',
                'u.email as student_email',
                'g.id as group_id',
                'g.name as group_name',
                'c.name as course_name',
                'e.academic_status',
                'e.payment_status',
                'er.final_grade',
                'er.attendance_percentage',
                'er.status as enrollment_status',
                'e.created_at as enrollment_date'
            ])
            ->where('e.academic_status', 'active')
            ->where('g.status', 'active')
            ->orderBy('u.name');

        // Aplicar filtros
        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('c.id', $filters['course_id']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('e.payment_status', $filters['payment_status']);
        }

        return new Collection($query->get());
    }

    /**
     * Listado de grupos con sus docentes
     */
    public function getGroupsWithTeachers(array $filters = []): Collection
    {
        $query = DB::table('groups as g')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->join('courses as c', 'cv.course_id', '=', 'c.id')
            ->leftJoin('group_teachers as gt', 'g.id', '=', 'gt.group_id')
            ->leftJoin('users as u', 'gt.user_id', '=', 'u.id')
            ->select([
                'g.id as group_id',
                'g.name as group_name',
                'g.start_date',
                'g.end_date',
                'g.status as group_status',
                'c.name as course_name',
                'u.id as teacher_id',
                'u.name as teacher_name',
                'u.email as teacher_email'
            ])
            ->orderBy('g.name')
            ->orderBy('u.name');

        // Aplicar filtros
        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('c.id', $filters['course_id']);
        }

        if (isset($filters['status'])) {
            $query->where('g.status', $filters['status']);
        }

        return new Collection($query->get());
    }

    /**
     * Listado de grupos y sus estudiantes
     */
    public function getGroupsWithStudents(array $filters = []): Collection
    {
        $query = DB::table('groups as g')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->join('courses as c', 'cv.course_id', '=', 'c.id')
            ->join('enrollments as e', 'g.id', '=', 'e.group_id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('enrollment_results as er', 'e.id', '=', 'er.enrollment_id')
            ->select([
                'g.id as group_id',
                'g.name as group_name',
                'g.start_date',
                'g.end_date',
                'g.status as group_status',
                'c.name as course_name',
                'u.id as student_id',
                'u.name as student_name',
                'u.email as student_email',
                'e.academic_status',
                'e.payment_status',
                'er.final_grade',
                'er.attendance_percentage',
                'e.created_at as enrollment_date'
            ])
            ->orderBy('g.name')
            ->orderBy('u.name');

        // Aplicar filtros
        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        if (isset($filters['academic_status'])) {
            $query->where('e.academic_status', $filters['academic_status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('e.payment_status', $filters['payment_status']);
        }

        return new Collection($query->get());
    }

    /**
     * Métricas básicas de asistencia por grupo
     */
    public function getAttendanceSummary(array $filters = []): Collection
    {
        $query = DB::table('attendances as a')
            ->join('class_sessions as cs', 'a.class_session_id', '=', 'cs.id')
            ->join('enrollments as e', 'a.enrollment_id', '=', 'e.id')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->select([
                'g.id as group_id',
                'g.name as group_name',
                'u.id as student_id',
                'u.name as student_name',
                DB::raw('COUNT(a.id) as total_sessions'),
                DB::raw('SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN a.status = "absent" THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN a.status = "late" THEN 1 ELSE 0 END) as late_count'),
                DB::raw('ROUND((SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id)), 2) as attendance_rate')
            ])
            ->groupBy('g.id', 'g.name', 'u.id', 'u.name')
            ->having('total_sessions', '>', 0)
            ->orderBy('g.name')
            ->orderBy('u.name');

        // Aplicar filtros
        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('u.id', $filters['student_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('cs.start_time', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('cs.start_time', '<=', $filters['end_date']);
        }

        return new Collection($query->get());
    }

    /**
     * Resumen de calificaciones por grupo
     */
    public function getGradesSummary(array $filters = []): Collection
    {
        $query = DB::table('grades as gr')
            ->join('exams as ex', 'gr.exam_id', '=', 'ex.id')
            ->join('enrollments as e', 'gr.enrollment_id', '=', 'e.id')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->join('modules as m', 'ex.module_id', '=', 'm.id')
            ->select([
                'g.id as group_id',
                'g.name as group_name',
                'u.id as student_id',
                'u.name as student_name',
                'm.id as module_id',
                'm.title as module_title',
                'ex.title as exam_title',
                'gr.grade',
                'ex.start_time as exam_date'
            ])
            ->orderBy('g.name')
            ->orderBy('u.name')
            ->orderBy('ex.start_time');

        // Aplicar filtros
        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('u.id', $filters['student_id']);
        }

        if (isset($filters['module_id'])) {
            $query->where('m.id', $filters['module_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('ex.start_time', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('ex.start_time', '<=', $filters['end_date']);
        }

        return new Collection($query->get());
    }

    /**
     * Listado de pagos pendientes y aprobados
     */
    public function getPaymentsSummary(array $filters = []): Collection
    {
        $query = DB::table('enrollment_payments as ep')
            ->join('enrollments as e', 'ep.enrollment_id', '=', 'e.id')
            ->join('users as u', 'e.user_id', '=', 'u.id')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->select([
                'ep.id as payment_id',
                'ep.operation_number',
                'ep.amount',
                'ep.status as payment_status',
                'ep.operation_date',
                'u.id as student_id',
                'u.name as student_name',
                'g.id as group_id',
                'g.name as group_name',
                'e.academic_status'
            ])
            ->orderBy('ep.operation_date', 'desc');

        // Aplicar filtros
        if (isset($filters['status'])) {
            $query->where('ep.status', $filters['status']);
        }

        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('ep.operation_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('ep.operation_date', '<=', $filters['end_date']);
        }

        return new Collection($query->get());
    }

    /**
     * Tickets de soporte por estado
     */
    public function getSupportTickets(array $filters = []): Collection
    {
        $query = DB::table('tickets as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->leftJoin('enrollments as e', 'u.id', '=', 'e.user_id')
            ->leftJoin('groups as g', 'e.group_id', '=', 'g.id')
            ->select([
                't.id as ticket_id',
                't.title as ticket_title',
                't.type as ticket_type',
                't.status as ticket_status',
                't.priority as ticket_priority',
                't.created_at',
                'u.id as student_id',
                'u.name as student_name',
                'g.id as group_id',
                'g.name as group_name'
            ])
            ->orderBy('t.created_at', 'desc');

        // Aplicar filtros
        if (isset($filters['status'])) {
            $query->where('t.status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('t.priority', $filters['priority']);
        }

        if (isset($filters['type'])) {
            $query->where('t.type', $filters['type']);
        }

        if (isset($filters['group_id'])) {
            $query->where('g.id', $filters['group_id']);
        }

        return new Collection($query->get());
    }

    /**
     * Citas programadas con docentes
     */
    public function getAppointments(array $filters = []): Collection
    {
        $query = DB::table('appointments as a')
            ->join('users as teacher', 'a.teacher_id', '=', 'teacher.id')
            ->join('users as student', 'a.student_id', '=', 'student.id')
            ->select([
                'a.id as appointment_id',
                'a.start_time',
                'a.end_time',
                'a.status as appointment_status',
                'a.meet_url',
                'teacher.id as teacher_id',
                'teacher.name as teacher_name',
                'student.id as student_id',
                'student.name as student_name'
            ])
            ->orderBy('a.start_time');

        // Aplicar filtros
        if (isset($filters['teacher_id'])) {
            $query->where('a.teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('a.student_id', $filters['student_id']);
        }

        if (isset($filters['status'])) {
            $query->where('a.status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->where('a.start_time', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('a.start_time', '<=', $filters['end_date']);
        }

        return new Collection($query->get());
    }

    /**
     * Dashboard con métricas rápidas
     */
    public function getQuickDashboard(): array
    {
        return [
            'total_students' => DB::table('enrollments')
                ->where('academic_status', 'active')
                ->distinct('user_id')
                ->count('user_id'),

            'total_groups' => DB::table('groups')
                ->where('status', 'active')
                ->count(),

            'pending_payments' => DB::table('enrollment_payments')
                ->where('status', 'pending')
                ->count(),

            'open_tickets' => DB::table('tickets')
                ->where('status', 'open')
                ->count(),

            'today_sessions' => DB::table('class_sessions')
                ->whereDate('start_time', today())
                ->count(),

            'upcoming_exams' => DB::table('exams')
                ->where('start_time', '>=', now())
                ->where('start_time', '<=', now()->addDays(7))
                ->count(),
        ];
    }
}