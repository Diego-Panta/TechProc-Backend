<?php

namespace App\Domains\DataAnalyst\Repositories;

use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Models\Course;
use App\Domains\Lms\Models\Attendance;
use App\Domains\Lms\Models\GradeRecord;
use App\Domains\DataAnalyst\Models\FinancialTransaction;
use App\Domains\SupportTechnical\Models\Ticket;
use App\Domains\SupportSecurity\Models\SecurityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExportReportRepository
{
    /**
     * Obtener datos para el reporte según tipo
     */
    public function getReportData(string $reportType, array $filters = [])
    {
        Log::info("Generando reporte: {$reportType}", ['filters' => $filters]);

        return match ($reportType) {
            'students' => $this->getStudentsReportData($filters),
            'courses' => $this->getCoursesReportData($filters),
            'attendance' => $this->getAttendanceReportData($filters),
            'grades' => $this->getGradesReportData($filters),
            'financial' => $this->getFinancialReportData($filters),
            'tickets' => $this->getTicketsReportData($filters),
            'security' => $this->getSecurityReportData($filters),
            'dashboard' => $this->getDashboardReportData($filters),
            default => $this->getEmptyReportData($reportType, $filters)
        };
    }

    /**
     * Aplicar filtros básicos comunes - FECHAS CORREGIDAS
     */
    private function applyBasicFilters($query, array $filters, string $dateField = 'created_at')
    {
        // DEBUG: Verificar qué filtros se están aplicando
        Log::debug("=== APLICANDO FILTROS BÁSICOS ===", [
            'filters_recibidos' => $filters,
            'date_field' => $dateField,
            'tiene_start_date' => !empty($filters['start_date']),
            'tiene_end_date' => !empty($filters['end_date']),
            'start_date_value' => $filters['start_date'] ?? 'NO',
            'end_date_value' => $filters['end_date'] ?? 'NO'
        ]);

        // Filtro por fecha - CORREGIDO
        if (!empty($filters['start_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $query->where($dateField, '>=', $startDate);
            Log::debug("✅ Aplicando filtro start_date: {$startDate} en campo {$dateField}");
        } else {
            Log::debug("❌ No hay start_date para aplicar");
        }

        if (!empty($filters['end_date'])) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();
            $query->where($dateField, '<=', $endDate);
            Log::debug("✅ Aplicando filtro end_date: {$endDate} en campo {$dateField}");
        } else {
            Log::debug("❌ No hay end_date para aplicar");
        }

        // Mostrar consulta final
        Log::debug("Consulta SQL final:", [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query;
    }

    /**
     * Aplicar filtros para estudiantes - CON ESTADO
     */
    private function applyStudentFilters($query, array $filters)
    {
        $query = $this->applyBasicFilters($query, $filters);

        // Filtro por estado (solo para estudiantes)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            Log::debug("Aplicando filtro status estudiantes: {$filters['status']}");
        }

        return $query;
    }

    /**
     * Aplicar filtros para cursos - SIN ESTADO
     */
    private function applyCourseFilters($query, array $filters)
    {
        // Solo aplicar filtros de fecha, NO estado
        return $this->applyBasicFilters($query, $filters);
    }

    /**
     * Aplicar filtros para tickets - CON ESTADO
     */
    private function applyTicketFilters($query, array $filters)
    {
        $query = $this->applyBasicFilters($query, $filters, 'creation_date');

        // Filtro por estado (solo para tickets)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
            Log::debug("Aplicando filtro status tickets: {$filters['status']}");
        }

        return $query;
    }

    /**
     * Aplicar filtros para otros reportes - SIN ESTADO
     */
    private function applyOtherFilters($query, array $filters, string $dateField = 'created_at')
    {
        // Solo aplicar filtros de fecha
        return $this->applyBasicFilters($query, $filters, $dateField);
    }

    /**
     * Datos para reporte de estudiantes - CON ESTADO
     */
    private function getStudentsReportData(array $filters)
    {
        try {
            $query = Student::query();
            $this->applyStudentFilters($query, $filters);

            $students = $query->get();

            Log::info("Consulta estudiantes ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $students->count()
            ]);

            $formattedData = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'codigo_estudiante' => $student->student_id,
                    'nombres' => $student->first_name,
                    'apellidos' => $student->last_name,
                    'email' => $student->email,
                    'telefono' => $student->phone,
                    'estado' => $this->safeStatus($student->status, ['active' => 'Activo', 'inactive' => 'Inactivo']),
                    'fecha_registro' => $this->safeDate($student->created_at),
                ];
            });

            Log::info("Reporte estudiantes generado", [
                'total' => $students->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'students',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $students->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte estudiantes: " . $e->getMessage());
            return $this->getErrorReportData('students', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para reporte de cursos - SIN ESTADO
     */
    private function getCoursesReportData(array $filters)
    {
        try {
            $query = Course::query();
            $this->applyCourseFilters($query, $filters);

            $courses = $query->get();

            Log::info("Consulta cursos ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $courses->count()
            ]);

            $formattedData = $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'titulo' => $course->title,
                    'nombre' => $course->name,
                    'nivel' => $this->safeTranslate($course->level, [
                        'basic' => 'Básico',
                        'intermediate' => 'Intermedio',
                        'advanced' => 'Avanzado'
                    ]),
                    'duracion_horas' => $course->duration,
                    'numero_sesiones' => $course->sessions,
                    'precio_venta' => $this->safeNumberFormat($course->selling_price),
                    'precio_descuento' => $this->safeNumberFormat($course->discount_price),
                    'estado' => $this->safeBoolStatus($course->status),
                    'fecha_creacion' => $this->safeDate($course->created_at),
                ];
            });

            Log::info("Reporte cursos generado", [
                'total' => $courses->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'courses',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $courses->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte cursos: " . $e->getMessage());
            return $this->getErrorReportData('courses', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para reporte de asistencia - SIN ESTADO
     */
    private function getAttendanceReportData(array $filters)
    {
        try {
            $query = Attendance::query();
            $this->applyOtherFilters($query, $filters, 'record_date');

            $attendances = $query->get();

            Log::info("Consulta asistencia ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $attendances->count()
            ]);

            $formattedData = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'id_participante_grupo' => $attendance->group_participant_id,
                    'id_clase' => $attendance->class_id,
                    'asistio' => $this->safeTranslate($attendance->attended, ['YES' => 'Sí', 'NO' => 'No']),
                    'hora_entrada' => $this->safeDate($attendance->entry_time, 'd/m/Y H:i'),
                    'hora_salida' => $this->safeDate($attendance->exit_time, 'd/m/Y H:i'),
                    'minutos_conectado' => $attendance->connected_minutes ?? 0,
                    'ip_conexion' => $attendance->connection_ip,
                    'dispositivo' => $attendance->device,
                    'ubicacion_aproximada' => $attendance->approximate_location,
                    'calidad_conexion' => $this->safeTranslate($attendance->connection_quality, [
                        'EXCELLENT' => 'Excelente',
                        'GOOD' => 'Buena',
                        'FAIR' => 'Regular',
                        'POOR' => 'Mala'
                    ]),
                    'observaciones' => $attendance->observations,
                    'fecha_registro' => $this->safeDate($attendance->record_date, 'd/m/Y H:i'),
                ];
            });

            Log::info("Reporte asistencia generado", [
                'total' => $attendances->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'attendance',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $attendances->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte asistencia: " . $e->getMessage());
            return $this->getErrorReportData('attendance', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para reporte de calificaciones - SIN ESTADO
     */
    private function getGradesReportData(array $filters)
    {
        try {
            $query = GradeRecord::query();
            $this->applyOtherFilters($query, $filters, 'record_date');

            $grades = $query->get();

            Log::info("Consulta calificaciones ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $grades->count()
            ]);

            $formattedData = $grades->map(function ($grade) {
                return [
                    'id' => $grade->id,
                    'id_usuario' => $grade->user_id,
                    'id_evaluacion' => $grade->evaluation_id,
                    'id_grupo' => $grade->group_id,
                    'calificacion_obtenida' => $this->safeNumberFormat($grade->obtained_grade, 2),
                    'peso_calificacion' => $this->safeNumberFormat($grade->grade_weight, 2),
                    'tipo_calificacion' => $this->safeTranslate($grade->grade_type, [
                        'Partial' => 'Parcial',
                        'Final' => 'Final',
                        'Makeup' => 'Recuperación'
                    ]),
                    'estado' => $this->safeTranslate($grade->status, [
                        'Recorded' => 'Registrada',
                        'Validated' => 'Validada',
                        'Published' => 'Publicada',
                        'Observed' => 'Observada'
                    ]),
                    'fecha_registro' => $this->safeDate($grade->record_date, 'd/m/Y H:i'),
                ];
            });

            Log::info("Reporte calificaciones generado", [
                'total' => $grades->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'grades',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $grades->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte calificaciones: " . $e->getMessage());
            return $this->getErrorReportData('grades', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para reporte financiero - SIN ESTADO
     */
    private function getFinancialReportData(array $filters)
    {
        try {
            $query = FinancialTransaction::query();
            $this->applyOtherFilters($query, $filters, 'transaction_date');

            $transactions = $query->get();

            Log::info("Consulta financiera ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $transactions->count()
            ]);

            $formattedData = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'id_cuenta' => $transaction->account_id,
                    'monto' => 'S/ ' . $this->safeNumberFormat($transaction->amount, 2),
                    'fecha_transaccion' => $this->safeDate($transaction->transaction_date, 'd/m/Y'),
                    'descripcion' => $transaction->description,
                    'tipo_transaccion' => $this->safeTranslate($transaction->transaction_type, [
                        'income' => 'Ingreso',
                        'expense' => 'Egreso'
                    ]),
                    'id_factura' => $transaction->invoice_id,
                    'id_pago' => $transaction->payment_id,
                    'fecha_creacion' => $this->safeDate($transaction->created_at, 'd/m/Y H:i'),
                ];
            });

            Log::info("Reporte financiero generado", [
                'total' => $transactions->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'financial',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $transactions->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte financiero: " . $e->getMessage());
            return $this->getErrorReportData('financial', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para reporte de tickets - CON ESTADO
     */
    private function getTicketsReportData(array $filters)
    {
        try {
            $query = Ticket::query();
            $this->applyTicketFilters($query, $filters);

            $tickets = $query->get();

            Log::info("Consulta tickets ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $tickets->count()
            ]);

            $formattedData = $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'codigo_ticket' => $ticket->ticket_id,
                    'id_tecnico_asignado' => $ticket->assigned_technician,
                    'id_usuario' => $ticket->user_id,
                    'titulo' => $ticket->title,
                    'descripcion' => $this->safeSubstring($ticket->description, 100),
                    'prioridad' => $this->safeTranslate($ticket->priority, [
                        'baja' => 'Baja',
                        'media' => 'Media',
                        'alta' => 'Alta',
                        'urgente' => 'Urgente'
                    ]),
                    'estado' => $this->safeTranslate($ticket->status, [
                        'abierto' => 'Abierto',
                        'en_progreso' => 'En Progreso',
                        'cerrado' => 'Cerrado'
                    ]),
                    'fecha_creacion' => $this->safeDate($ticket->creation_date, 'd/m/Y H:i'),
                    'fecha_asignacion' => $this->safeDate($ticket->assignment_date, 'd/m/Y H:i'),
                    'fecha_resolucion' => $this->safeDate($ticket->resolution_date, 'd/m/Y H:i'),
                    'fecha_cierre' => $this->safeDate($ticket->close_date, 'd/m/Y H:i'),
                    'categoria' => $ticket->category,
                ];
            });

            Log::info("Reporte tickets generado", [
                'total' => $tickets->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'tickets',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $tickets->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte tickets: " . $e->getMessage());
            return $this->getErrorReportData('tickets', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para reporte de seguridad - SIN ESTADO
     */
    private function getSecurityReportData(array $filters)
    {
        try {
            $query = SecurityLog::query();
            $this->applyOtherFilters($query, $filters, 'event_date');

            $logs = $query->get();

            Log::info("Consulta seguridad ejecutada", [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
                'total' => $logs->count()
            ]);

            $formattedData = $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'codigo_log' => $log->id_security_log,
                    'id_usuario' => $log->user_id,
                    'tipo_evento' => $this->safeTranslate($log->event_type, [
                        'login' => 'Inicio de Sesión',
                        'logout' => 'Cierre de Sesión',
                        'failed_login' => 'Intento Fallido',
                        'password_change' => 'Cambio de Contraseña',
                        'security_alert' => 'Alerta de Seguridad'
                    ]),
                    'descripcion' => $this->safeSubstring($log->description, 100),
                    'ip_origen' => $log->source_ip,
                    'fecha_evento' => $this->safeDate($log->event_date, 'd/m/Y H:i'),
                ];
            });

            Log::info("Reporte seguridad generado", [
                'total' => $logs->count(),
                'filtros_aplicados' => $filters
            ]);

            return [
                'data' => $formattedData,
                'metadata' => [
                    'report_type' => 'security',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                    'total_records' => $logs->count()
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en reporte seguridad: " . $e->getMessage());
            return $this->getErrorReportData('security', $filters, $e->getMessage());
        }
    }

    /**
     * Datos para dashboard - SIN FILTROS
     */
    private function getDashboardReportData(array $filters)
    {
        try {
            $data = [
                'total_estudiantes' => Student::count(),
                'total_cursos' => Course::count(),
                'tickets_activos' => Ticket::where('status', '!=', 'cerrado')->count(),
                'transacciones_recientes' => FinancialTransaction::where('transaction_date', '>=', Carbon::now()->subDays(30))->count(),
                'eventos_seguridad' => SecurityLog::where('event_date', '>=', Carbon::now()->subDays(7))->count(),
                'total_asistencias' => Attendance::where('attended', 'YES')->count(),
            ];

            Log::info("Dashboard generado", $data);

            return [
                'data' => $data,
                'metadata' => [
                    'report_type' => 'dashboard',
                    'filters_applied' => $filters,
                    'generated_at' => Carbon::now(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error en dashboard: " . $e->getMessage());
            return $this->getErrorReportData('dashboard', $filters, $e->getMessage());
        }
    }

    /**
     * Métodos auxiliares para manejo seguro de datos
     */

    private function safeDate($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return '-';
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return '-';
        }
    }

    private function safeStatus($status, array $map = [])
    {
        if (is_object($status)) {
            Log::warning("Status es objeto: " . get_class($status));
            return 'Desconocido';
        }

        return $map[$status] ?? $status ?? 'Desconocido';
    }

    private function safeBoolStatus($status)
    {
        if (is_bool($status)) {
            return $status ? 'Activo' : 'Inactivo';
        }
        return $status ? 'Activo' : 'Inactivo';
    }

    private function safeTranslate($value, array $map = [])
    {
        if (is_object($value)) {
            Log::warning("Valor es objeto en traducción: " . get_class($value));
            return 'Desconocido';
        }

        return $map[$value] ?? $value ?? '-';
    }

    private function safeNumberFormat($number, $decimals = 2)
    {
        if (!is_numeric($number)) return '0.00';
        return number_format(floatval($number), $decimals);
    }

    private function safeSubstring($text, $length)
    {
        if (!$text) return '-';
        $text = (string) $text;
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Datos para reporte vacío (error)
     */
    private function getErrorReportData(string $reportType, array $filters, string $error)
    {
        Log::error("Reporte {$reportType} falló: {$error}");

        return [
            'data' => collect([['error' => "Error generando reporte: {$error}"]]),
            'metadata' => [
                'report_type' => $reportType,
                'filters_applied' => $filters,
                'generated_at' => Carbon::now(),
                'total_records' => 0,
                'error' => $error
            ]
        ];
    }

    private function getEmptyReportData(string $reportType, array $filters)
    {
        return [
            'data' => collect([['mensaje' => 'Tipo de reporte no reconocido']]),
            'metadata' => [
                'report_type' => $reportType,
                'filters_applied' => $filters,
                'generated_at' => Carbon::now(),
                'total_records' => 0
            ]
        ];
    }

    /**
     * Obtener opciones de filtro - SOLO ESTADO PARA ESTUDIANTES Y TICKETS
     */
    public function getFilterOptions($reportType): array
    {
        $options = [];

        // Solo mostrar filtro de estado para estudiantes y tickets
        if (in_array($reportType, ['students', 'tickets'])) {
            $options['statuses'] = $this->getStatusOptions($reportType);
        }

        Log::debug("Opciones de filtro para {$reportType}", $options);

        return $options;
    }

    private function getStatusOptions($reportType): array
    {
        return match ($reportType) {
            'students' => ['active', 'inactive'],
            'tickets' => ['abierto', 'en_progreso', 'cerrado'],
            default => []
        };
    }
}
