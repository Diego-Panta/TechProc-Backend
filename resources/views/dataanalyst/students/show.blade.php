@extends('dataanalyst.layout')

@section('title', 'Detalle del Estudiante')

@section('content')
<h2>Detalle del Estudiante</h2>

<a href="{{ route('dataanalyst.students.index') }}?{{ http_build_query(request()->query()) }}">← Volver al listado</a>

<!-- Datos Personales -->
<h3>Datos Personales</h3>
<table border="1" style="width: 100%; border-collapse: collapse;">
    <tr>
        <th style="width: 200px; padding: 8px;">Nombre Completo</th>
        <td style="padding: 8px;">{{ $student->first_name }} {{ $student->last_name }}</td>
    </tr>
    <tr>
        <th style="padding: 8px;">Email</th>
        <td style="padding: 8px;">{{ $student->email }}</td>
    </tr>
    <tr>
        <th style="padding: 8px;">Teléfono</th>
        <td style="padding: 8px;">{{ $student->phone ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th style="padding: 8px;">Documento</th>
        <td style="padding: 8px;">{{ $student->document_number ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th style="padding: 8px;">Estado</th>
        <td style="padding: 8px;">
            @if($student->status == 'active')
            <span style="color: green;">● Activo</span>
            @else
            <span style="color: red;">● Inactivo</span>
            @endif
        </td>
    </tr>
    <tr>
        <th style="padding: 8px;">Fecha de Registro</th>
        <td style="padding: 8px;">{{ $student->created_at->format('d/m/Y H:i') }}</td>
    </tr>
</table>

<!-- Empresa -->
<h3>Empresa Asociada</h3>
@if($student->company)
<table border="1" style="width: 100%; border-collapse: collapse;">
    <tr>
        <th style="width: 200px; padding: 8px;">Nombre</th>
        <td style="padding: 8px;">{{ $student->company->name }}</td>
    </tr>
    <tr>
        <th style="padding: 8px;">Industria</th>
        <td style="padding: 8px;">{{ $student->company->industry ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th style="padding: 8px;">Contacto</th>
        <td style="padding: 8px;">
            {{ $student->company->contact_name ?? 'N/A' }}
            ({{ $student->company->contact_email ?? 'N/A' }})
        </td>
    </tr>
</table>
@else
<p>No tiene empresa asociada</p>
@endif

<!-- Matrículas -->
<h3>Historial de Matrículas ({{ $student->enrollments->count() }})</h3>
@if($student->enrollments->count() > 0)
<table border="1" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="padding: 8px;">ID Matrícula</th>
            <th style="padding: 8px;">Fecha Matrícula</th>
            <th style="padding: 8px;">Tipo</th>
            <th style="padding: 8px;">Estado</th>
            <th style="padding: 8px;">Periodo Académico</th>
            <th style="padding: 8px;">Detalles</th>
        </tr>
    </thead>
    <tbody>
        @foreach($student->enrollments as $enrollment)
        <tr>
            <td style="padding: 8px;">{{ $enrollment->enrollment_id ?? 'N/A' }}</td>
            <td style="padding: 8px;">{{ $enrollment->enrollment_date->format('d/m/Y') }}</td>
            <td style="padding: 8px;">{{ $enrollment->enrollment_type }}</td>
            <td style="padding: 8px;">
                @if($enrollment->status == 'active')
                <span style="color: green;">● Activo</span>
                @else
                <span style="color: orange;">● {{ $enrollment->status }}</span>
                @endif
            </td>
            <td style="padding: 8px;">{{ $enrollment->academicPeriod->name ?? 'N/A' }}</td>
            <td style="padding: 8px;">
                @if($enrollment->enrollmentDetails->count() > 0)
                {{ $enrollment->enrollmentDetails->count() }} curso(s)
                @else
                Sin detalles
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p>No tiene matrículas registradas</p>
@endif
@endsection