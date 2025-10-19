@extends('dataanalyst.layout')

@section('title', 'Listado de Estudiantes')

@section('content')
<h2>Listado General de Estudiantes</h2>

<!-- Formulario de Filtros -->
<form method="GET" action="{{ route('dataanalyst.students.index') }}">
    <div>
        <label>Buscar (Nombre/Apellido/Email):</label>
        <input type="text" name="search" value="{{ request('search') }}">
    </div>

    <div>
        <label>Empresa:</label>
        <input type="text" name="company" value="{{ request('company') }}">
    </div>

    <div>
        <label>Estado:</label>
        <select name="status">
            <option value="">Todos</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>

    <div>
        <label>Fecha desde:</label>
        <input type="date" name="start_date" value="{{ request('start_date') }}">
    </div>

    <div>
        <label>Fecha hasta:</label>
        <input type="date" name="end_date" value="{{ request('end_date') }}">
    </div>

    <div>
        <label>Registros por página:</label>
        <select name="per_page">
            <option value="15" {{ (request('per_page') ?? 15) == 15 ? 'selected' : '' }}>15</option>
            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
        </select>
    </div>

    <button type="submit">Filtrar</button>
    <a href="{{ route('dataanalyst.students.index') }}">Limpiar</a>
</form>

<!-- Estadísticas rápidas -->
<div style="margin: 20px 0; padding: 10px; border: 1px solid #ccc;">
    <strong>Resumen:</strong> 
    Total: {{ $students->total() }} estudiantes | 
    Mostrando: {{ $students->count() }} registros
</div>

<!-- Tabla de Estudiantes -->
@if($students->count() > 0)
<table border="1" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>Nombre Completo</th>
            <th>Email</th>
            <th>Empresa</th>
            <th>Estado</th>
            <th>Fecha Creación</th>
            <th>Total Matrículas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $student)
        <tr>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->company->name ?? 'N/A' }}</td>
            <td>
                @if($student->status == 'active')
                    <span style="color: green;">● Activo</span>
                @else
                    <span style="color: red;">● Inactivo</span>
                @endif
            </td>
            <td>{{ $student->created_at->format('d/m/Y') }}</td>
            <td style="text-align: center;">{{ $student->enrollments_count }}</td>
            <td>
                <a href="{{ route('dataanalyst.students.show', $student->id) }}">Ver Detalle</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Paginación -->
<div style="margin-top: 20px;">
    {{ $students->appends(request()->query())->links() }}
</div>
@else
<p>No se encontraron estudiantes con los filtros aplicados.</p>
@endif

<!-- Enlace a estadísticas API -->
<div style="margin-top: 20px;">
    <a href="{{ route('dataanalyst.students.statistics') }}?{{ http_build_query(request()->query()) }}">
        Ver Estadísticas Completas (JSON)
    </a>
</div>
@endsection