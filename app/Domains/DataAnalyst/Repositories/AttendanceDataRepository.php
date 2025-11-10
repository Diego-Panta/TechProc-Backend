<?php
// app/Domains/DataAnalyst/Repositories/AttendanceDataRepository.php

namespace App\Domains\DataAnalyst\Repositories;

use Illuminate\Support\Facades\DB;
use IncadevUns\CoreDomain\Enums\AttendanceStatus;
use IncadevUns\CoreDomain\Enums\EnrollmentAcademicStatus;
use IncadevUns\CoreDomain\Enums\GroupStatus;

class AttendanceDataRepository
{
    /**
     * Obtiene datos detallados de asistencia para una matrícula específica
     */
    public function getStudentAttendanceData(int $enrollmentId, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);

        $presentStatus = AttendanceStatus::Present->value;
        $absentStatus = AttendanceStatus::Absent->value;
        $lateStatus = AttendanceStatus::Late->value;
        $activeStatus = EnrollmentAcademicStatus::Active->value;

        // Consulta principal mejorada
        $data = DB::table('enrollments as e')
            ->select([
                'e.id as enrollment_id',
                'e.group_id',
                'e.user_id',
                'g.name as group_name',
                'g.start_date as group_start_date',
                'g.end_date as group_end_date',
                'cv.course_id',
                'c.name as course_name',
                
                // Estadísticas reales - CORREGIDAS
                DB::raw('COALESCE((
                    SELECT COUNT(*) FROM class_sessions cs 
                    WHERE cs.group_id = e.group_id 
                    AND cs.start_time BETWEEN ? AND ?
                ), 0) as total_sessions'),
                
                DB::raw("COALESCE((
                    SELECT COUNT(*) FROM attendances a 
                    JOIN class_sessions cs ON a.class_session_id = cs.id 
                    WHERE a.enrollment_id = e.id 
                    AND a.status = '{$presentStatus}'
                    AND cs.start_time BETWEEN ? AND ?
                ), 0) as attended_sessions"),
                
                DB::raw("COALESCE((
                    SELECT COUNT(*) FROM attendances a 
                    JOIN class_sessions cs ON a.class_session_id = cs.id 
                    WHERE a.enrollment_id = e.id 
                    AND a.status = '{$absentStatus}'
                    AND cs.start_time BETWEEN ? AND ?
                ), 0) as absent_sessions"),
                
                DB::raw("COALESCE((
                    SELECT COUNT(*) FROM attendances a 
                    JOIN class_sessions cs ON a.class_session_id = cs.id 
                    WHERE a.enrollment_id = e.id 
                    AND a.status = '{$lateStatus}'
                    AND cs.start_time BETWEEN ? AND ?
                ), 0) as late_sessions"),
                
                // Ausencias consecutivas - CORREGIDO
                DB::raw("COALESCE((
                    SELECT MAX(consecutive_count) FROM (
                         SELECT COUNT(*) as consecutive_count 
                         FROM attendances a 
                         JOIN class_sessions cs ON a.class_session_id = cs.id 
                         WHERE a.enrollment_id = e.id 
                         AND a.status = '{$absentStatus}'
                         AND cs.start_time BETWEEN ? AND ?
                         GROUP BY DATE(cs.start_time)
                     ) as absences
                ), 0) as max_consecutive_absences"),
                
                // Última asistencia - CORREGIDO
                DB::raw("(
                    SELECT MAX(cs.start_time) FROM attendances a 
                    JOIN class_sessions cs ON a.class_session_id = cs.id 
                    WHERE a.enrollment_id = e.id 
                    AND a.status = '{$presentStatus}'
                    AND cs.start_time BETWEEN ? AND ?
                ) as last_attendance_date"),
            ])
            ->addBinding([$dateRange['start'], $dateRange['end']], 'select')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'select')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'select')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'select')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'select')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'select')
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->join('courses as c', 'cv.course_id', '=', 'c.id')
            ->where('e.id', $enrollmentId)
            ->where('e.academic_status', $activeStatus)
            ->where('g.status', GroupStatus::Active->value)
            ->first();

        if (!$data) {
            return $this->getEmptyStudentData($enrollmentId);
        }

        $baseData = (array) $data;

        // Agregar datos adicionales SOLO si existen sesiones en el período
        if ($baseData['total_sessions'] > 0) {
            return array_merge($baseData, [
                'attendance_timeline' => $this->getAttendanceTimeline($enrollmentId, $dateRange),
                'comparison_with_group' => $this->getGroupComparison($baseData['group_id'], $dateRange),
                'attendance_by_module' => $this->getAttendanceByModule($enrollmentId, $dateRange),
                'weekly_pattern' => $this->getWeeklyPattern($enrollmentId, $dateRange),
            ]);
        }

        return $baseData;
    }

    /**
     * Datos vacíos para estudiantes sin sesiones en el período
     */
    private function getEmptyStudentData(int $enrollmentId): array
    {
        $enrollmentData = DB::table('enrollments as e')
            ->select([
                'e.id as enrollment_id',
                'e.group_id',
                'e.user_id',
                'g.name as group_name',
                'g.start_date as group_start_date',
                'g.end_date as group_end_date',
                'cv.course_id',
                'c.name as course_name',
            ])
            ->join('groups as g', 'e.group_id', '=', 'g.id')
            ->join('course_versions as cv', 'g.course_version_id', '=', 'cv.id')
            ->join('courses as c', 'cv.course_id', '=', 'c.id')
            ->where('e.id', $enrollmentId)
            ->where('e.academic_status', EnrollmentAcademicStatus::Active->value)
            ->where('g.status', GroupStatus::Active->value)
            ->first();

        if (!$enrollmentData) {
            return [
                'enrollment_id' => $enrollmentId,
                'total_sessions' => 0,
                'attended_sessions' => 0,
                'absent_sessions' => 0,
                'late_sessions' => 0,
                'max_consecutive_absences' => 0,
                'last_attendance_date' => null,
                'group_name' => 'No disponible',
                'course_name' => 'No disponible',
                'group_start_date' => null,
                'group_end_date' => null,
            ];
        }

        return array_merge((array) $enrollmentData, [
            'total_sessions' => 0,
            'attended_sessions' => 0,
            'absent_sessions' => 0,
            'late_sessions' => 0,
            'max_consecutive_absences' => 0,
            'last_attendance_date' => null,
        ]);
    }

    /**
     * Obtiene estadísticas de asistencia para un grupo completo - CORREGIDO
     */
    public function getGroupAttendanceData(int $groupId, string $period = '30d'): array
    {
        $dateRange = $this->getDateRange($period);

        $presentStatus = AttendanceStatus::Present->value;
        $activeStatus = EnrollmentAcademicStatus::Active->value;

        // Consulta mejorada para grupos
        $data = DB::table('enrollments as e')
            ->select([
                'e.group_id',
                DB::raw('COUNT(DISTINCT e.id) as total_students'),
                DB::raw("COALESCE(AVG(
                    CASE WHEN cs_total.total_sessions > 0 THEN 
                        (att.attended_count * 100.0 / cs_total.total_sessions)
                    ELSE 100.0 END  -- Si no hay sesiones, consideramos 100% de asistencia
                ), 100.0) as group_attendance_rate"),
                
                DB::raw("SUM(
                    CASE WHEN cs_total.total_sessions > 0 AND 
                         (att.attended_count * 100.0 / cs_total.total_sessions) < 70 
                    THEN 1 ELSE 0 END
                ) as at_risk_students"),
            ])
            ->leftJoin(DB::raw("(
                SELECT enrollment_id, 
                       COUNT(CASE WHEN status = '{$presentStatus}' THEN 1 END) as attended_count
                FROM attendances a
                JOIN class_sessions cs ON a.class_session_id = cs.id
                WHERE cs.start_time BETWEEN ? AND ?
                GROUP BY enrollment_id
            ) as att"), 'e.id', '=', 'att.enrollment_id')
            ->leftJoin(DB::raw("(
                SELECT group_id, COUNT(*) as total_sessions
                FROM class_sessions 
                WHERE start_time BETWEEN ? AND ?
                GROUP BY group_id
            ) as cs_total"), 'e.group_id', '=', 'cs_total.group_id')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'join')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'join')
            ->where('e.group_id', $groupId)
            ->where('e.academic_status', $activeStatus)
            ->groupBy('e.group_id')
            ->first();

        return $data ? (array) $data : [
            'group_id' => $groupId,
            'total_students' => 0,
            'group_attendance_rate' => 100.0, // Sin sesiones = 100% por defecto
            'at_risk_students' => 0,
        ];
    }

    /**
     * Obtiene timeline de asistencia
     */
    private function getAttendanceTimeline(int $enrollmentId, array $dateRange): array
    {
        $presentStatus = AttendanceStatus::Present->value;
        $absentStatus = AttendanceStatus::Absent->value;
        $lateStatus = AttendanceStatus::Late->value;

        return DB::table('attendances as a')
            ->selectRaw("
                DATE(cs.start_time) as date,
                COUNT(*) as total_sessions,
                SUM(CASE WHEN a.status = '{$presentStatus}' THEN 1 ELSE 0 END) as attended,
                SUM(CASE WHEN a.status = '{$absentStatus}' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = '{$lateStatus}' THEN 1 ELSE 0 END) as late
            ")
            ->join('class_sessions as cs', 'a.class_session_id', '=', 'cs.id')
            ->where('a.enrollment_id', $enrollmentId)
            ->whereBetween('cs.start_time', [$dateRange['start'], $dateRange['end']])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Obtiene patrón semanal
     */
    private function getWeeklyPattern(int $enrollmentId, array $dateRange): array
    {
        $presentStatus = AttendanceStatus::Present->value;

        $weeklyData = DB::table('attendances as a')
            ->selectRaw("
                DAYOFWEEK(cs.start_time) as day_of_week,
                COUNT(*) as total_sessions,
                SUM(CASE WHEN a.status = '{$presentStatus}' THEN 1 ELSE 0 END) as attended_sessions,
                SUM(CASE WHEN a.status = '{$presentStatus}' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as attendance_rate
            ")
            ->join('class_sessions as cs', 'a.class_session_id', '=', 'cs.id')
            ->where('a.enrollment_id', $enrollmentId)
            ->whereBetween('cs.start_time', [$dateRange['start'], $dateRange['end']])
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get();

        $weeklyPattern = [];
        $days = [
            1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles',
            5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
        ];

        foreach ($weeklyData as $day) {
            $weeklyPattern[$day->day_of_week] = [
                'day_name' => $days[$day->day_of_week] ?? 'Desconocido',
                'total_sessions' => $day->total_sessions,
                'attended_sessions' => $day->attended_sessions,
                'attendance_rate' => (float) $day->attendance_rate,
            ];
        }

        return $weeklyPattern;
    }

    /**
     * Compara con el promedio del grupo - CORREGIDO
     */
    private function getGroupComparison(int $groupId, array $dateRange): array
    {
        $presentStatus = AttendanceStatus::Present->value;
        $activeStatus = EnrollmentAcademicStatus::Active->value;

        $groupData = DB::table('enrollments as e')
            ->selectRaw("
                AVG(
                    CASE WHEN cs_total.total_sessions > 0 THEN 
                        (att.attended_count * 100.0 / cs_total.total_sessions)
                    ELSE 100.0 END
                ) as avg_attendance_rate,
                COUNT(DISTINCT e.id) as total_students
            ")
            ->leftJoin(DB::raw("(
                SELECT enrollment_id, 
                       COUNT(CASE WHEN status = '{$presentStatus}' THEN 1 END) as attended_count
                FROM attendances a
                JOIN class_sessions cs ON a.class_session_id = cs.id
                WHERE cs.start_time BETWEEN ? AND ?
                GROUP BY enrollment_id
            ) as att"), 'e.id', '=', 'att.enrollment_id')
            ->leftJoin(DB::raw("(
                SELECT group_id, COUNT(*) as total_sessions
                FROM class_sessions 
                WHERE start_time BETWEEN ? AND ?
                GROUP BY group_id
            ) as cs_total"), 'e.group_id', '=', 'cs_total.group_id')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'join')
            ->addBinding([$dateRange['start'], $dateRange['end']], 'join')
            ->where('e.group_id', $groupId)
            ->where('e.academic_status', $activeStatus)
            ->first();

        return [
            'group_average' => (float) ($groupData->avg_attendance_rate ?? 100.0),
            'total_students' => $groupData->total_students ?? 0,
        ];
    }

    /**
     * Obtiene asistencia por módulo
     */
    private function getAttendanceByModule(int $enrollmentId, array $dateRange): array
    {
        $presentStatus = AttendanceStatus::Present->value;

        return DB::table('attendances as a')
            ->select([
                'm.id as module_id',
                'm.title as module_name',
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw("SUM(CASE WHEN a.status = '{$presentStatus}' THEN 1 ELSE 0 END) as attended_sessions"),
                DB::raw("SUM(CASE WHEN a.status = '{$presentStatus}' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as attendance_rate")
            ])
            ->join('class_sessions as cs', 'a.class_session_id', '=', 'cs.id')
            ->join('modules as m', 'cs.module_id', '=', 'm.id')
            ->where('a.enrollment_id', $enrollmentId)
            ->whereBetween('cs.start_time', [$dateRange['start'], $dateRange['end']])
            ->groupBy('m.id', 'm.title')
            ->orderBy('m.sort')
            ->get()
            ->toArray();
    }

    /**
     * Define el rango de fechas para el análisis
     */
    private function getDateRange(string $period): array
    {
        return match($period) {
            '7d' => ['start' => now()->subDays(7), 'end' => now()],
            '30d' => ['start' => now()->subDays(30), 'end' => now()],
            '90d' => ['start' => now()->subDays(90), 'end' => now()],
            'all' => ['start' => now()->subYear(), 'end' => now()],
            default => ['start' => now()->subDays(30), 'end' => now()],
        };
    }
}