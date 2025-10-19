<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Asistencia - DataAnalyst</title>
</head>
<body>
    <h1>Reporte de Asistencia</h1>

    <!-- Formulario de Filtros -->
    <form method="GET" action="{{ route('dataanalyst.attendance.index') }}">
        <div>
            <label for="group_id">Grupo ID:</label>
            <input type="number" id="group_id" name="group_id" value="{{ request('group_id') }}">
        </div>
        
        <div>
            <label for="course_id">Curso:</label>
            <select id="course_id" name="course_id">
                <option value="">Todos los cursos</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                    {{ $course->title }}
                </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="student_id">Estudiante:</label>
            <select id="student_id" name="student_id">
                <option value="">Todos los estudiantes</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                    {{ $student->first_name }} {{ $student->last_name }} ({{ $student->email }})
                </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="start_date">Fecha Inicio:</label>
            <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}">
        </div>
        
        <div>
            <label for="end_date">Fecha Fin:</label>
            <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}">
        </div>
        
        <div>
            <label for="attendance_status">Estado Asistencia:</label>
            <select id="attendance_status" name="attendance_status">
                <option value="">Todos</option>
                <option value="YES" {{ request('attendance_status') == 'YES' ? 'selected' : '' }}>Presente</option>
                <option value="NO" {{ request('attendance_status') == 'NO' ? 'selected' : '' }}>Ausente</option>
            </select>
        </div>
        
        <button type="submit">Filtrar</button>
        <a href="{{ route('dataanalyst.attendance.index') }}">Limpiar</a>
    </form>

    <!-- Estadísticas Rápidas -->
    <div>
        <h2>Estadísticas</h2>
        <div id="statistics">
            <!-- Las estadísticas se cargarán aquí -->
        </div>
    </div>

    <!-- Tabla de Asistencias -->
    <h2>Registros de Asistencia</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Fecha Clase</th>
                <th>Grupo</th>
                <th>Curso</th>
                <th>Estudiante</th>
                <th>Email</th>
                <th>Asistió</th>
                <th>Minutos Conectado</th>
                <th>Calidad Conexión</th>
                <th>Dispositivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceData as $attendance)
            @php
                $user = $attendance->groupParticipant->user;
                $student = $user->student ?? null;
                
                // Obtener el nombre del estudiante
                $studentName = $student 
                    ? $student->first_name . ' ' . $student->last_name
                    : ($user->full_name ?? $user->first_name . ' ' . $user->last_name);
                
                $studentEmail = $student->email ?? $user->email;
            @endphp
            <tr>
                <td>{{ $attendance->class->class_date }} {{ $attendance->class->start_time->format('H:i') }}</td>
                <td>{{ $attendance->class->group->name }}</td>
                <td>{{ $attendance->class->group->course->title }}</td>
                <td>{{ $studentName }}</td>
                <td>{{ $studentEmail }}</td>
                <td>{{ $attendance->attended }}</td>
                <td>{{ $attendance->connected_minutes }}</td>
                <td>{{ $attendance->connection_quality }}</td>
                <td>{{ $attendance->device }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación -->
    <div>
        {{ $attendanceData->links() }}
    </div>

    <!-- Script para cargar estadísticas -->
    <script>
        // Cargar estadísticas al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            fetch('{{ route("dataanalyst.attendance.statistics") }}?' + new URLSearchParams(window.location.search))
                .then(response => response.json())
                .then(data => {
                    document.getElementById('statistics').innerHTML = `
                        <p><strong>Total Clases:</strong> ${data.total_classes}</p>
                        <p><strong>Total Asistencias Registradas:</strong> ${data.total_attendances_recorded}</p>
                        <p><strong>Asistencias Presentes:</strong> ${data.present_attendances}</p>
                        <p><strong>Asistencias Ausentes:</strong> ${data.absent_attendances}</p>
                        <p><strong>Tasa Promedio de Asistencia:</strong> ${data.average_attendance_rate}%</p>
                    `;
                });
        });
    </script>
</body>
</html>