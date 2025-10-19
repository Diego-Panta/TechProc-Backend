<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Calificaciones - Data Analyst</title>
</head>
<body>
    <h1>Reporte de Calificaciones</h1>
    
    <!-- Filtros Simplificados -->
    <form method="GET" action="{{ route('dataanalyst.grades.index') }}">
        <div>
            <label>Curso:</label>
            <select name="course_id">
                <option value="">Todos los cursos</option>
                @foreach($filterData['courses'] as $course)
                    <option value="{{ $course->id }}" 
                        {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->name }} - {{ $course->title }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label>Período Académico:</label>
            <select name="academic_period_id">
                <option value="">Todos los períodos</option>
                @foreach($filterData['academicPeriods'] as $period)
                    <option value="{{ $period->id }}" 
                        {{ request('academic_period_id') == $period->id ? 'selected' : '' }}>
                        {{ $period->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label>Tipo de Calificación:</label>
            <select name="grade_type">
                <option value="">Todos los tipos</option>
                <option value="Partial" {{ request('grade_type') == 'Partial' ? 'selected' : '' }}>Parcial</option>
                <option value="Final" {{ request('grade_type') == 'Final' ? 'selected' : '' }}>Final</option>
                <option value="Makeup" {{ request('grade_type') == 'Makeup' ? 'selected' : '' }}>Recuperación</option>
            </select>
        </div>
        
        <div>
            <label>Fecha Inicio:</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>
        
        <div>
            <label>Fecha Fin:</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>
        
        <button type="submit">Filtrar</button>
        <a href="{{ route('dataanalyst.grades.index') }}">Limpiar Filtros</a>
    </form>

    <!-- Estadísticas -->
    <h2>Estadísticas Generales</h2>
    <div id="statistics">
        <p>Cargando estadísticas...</p>
    </div>

    <!-- Tabla de calificaciones -->
    <h2>Calificaciones Registradas</h2>
    
    @if($gradesReport->count() > 0)
    <table border="1">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Curso</th>
                <th>Grupo</th>
                <th>Calificación</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gradesReport as $grade)
            <tr>
                <td>
                    @if($grade->user && $grade->user->student)
                        {{ $grade->user->student->first_name }} {{ $grade->user->student->last_name }}
                    @else
                        Usuario #{{ $grade->user_id }}
                    @endif
                </td>
                <td>
                    @if($grade->group && $grade->group->course)
                        {{ $grade->group->course->name }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $grade->group->name ?? 'N/A' }}</td>
                <td><strong>{{ $grade->obtained_grade }}</strong></td>
                <td>{{ $grade->grade_type }}</td>
                <td>{{ $grade->status }}</td>
                <td>{{ $grade->record_date->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación -->
    <div>
        {{ $gradesReport->links() }}
    </div>
    @else
    <p>No se encontraron calificaciones con los filtros aplicados.</p>
    @endif

    <script>
        // Cargar estadísticas al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });

        function loadStatistics() {
            // Construir URL con los mismos filtros del formulario
            const params = new URLSearchParams(window.location.search);
            fetch(`{{ route("dataanalyst.grades.statistics") }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('statistics').innerHTML = `
                        <p><strong>Total de calificaciones:</strong> ${data.total_grades_recorded}</p>
                        <p><strong>Promedio general:</strong> ${data.average_grade}</p>
                        <p><strong>Tasa de aprobación:</strong> ${data.passing_rate}%</p>
                        
                        <h3>Estadísticas por Grupo:</h3>
                        ${data.by_group.length > 0 ? 
                            data.by_group.map(group => `
                                <p><strong>${group.group_name}</strong> (${group.course_name}): 
                                Promedio ${parseFloat(group.average_grade).toFixed(1)}, 
                                Aprobación ${parseFloat(group.passing_rate).toFixed(1)}%</p>
                            `).join('') : 
                            '<p>No hay datos por grupo</p>'
                        }
                        
                        <h3>Mejores Estudiantes:</h3>
                        ${data.top_performers.length > 0 ? 
                            data.top_performers.map(student => `
                                <p>${student.first_name} ${student.last_name}: 
                                Promedio ${parseFloat(student.average_grade).toFixed(1)}</p>
                            `).join('') : 
                            '<p>No hay datos de mejores estudiantes</p>'
                        }
                    `;
                })
                .catch(error => {
                    document.getElementById('statistics').innerHTML = '<p>Error al cargar estadísticas</p>';
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>