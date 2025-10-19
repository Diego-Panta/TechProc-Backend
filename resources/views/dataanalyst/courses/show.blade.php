<!DOCTYPE html>
<html>
<head>
    <title>Detalle Curso - Data Analyst</title>
</head>
<body>
    <h1>Detalle del Curso: {{ $course->title }}</h1>
    
    <div>
        <h2>Información Básica</h2>
        <p><strong>ID:</strong> {{ $course->id }}</p>
        <p><strong>Título:</strong> {{ $course->title }}</p>
        <p><strong>Nombre:</strong> {{ $course->name }}</p>
        <p><strong>Nivel:</strong> {{ $course->level }}</p>
        <p><strong>Duración:</strong> {{ $course->duration }} horas</p>
        <p><strong>Sesiones:</strong> {{ $course->sessions }}</p>
        <p><strong>Precio:</strong> ${{ number_format($course->selling_price, 2) }}</p>
        <p><strong>Estado:</strong> {{ $course->status ? 'Activo' : 'Inactivo' }}</p>
    </div>

    <div>
        <h2>Categorías</h2>
        <ul>
            @foreach($course->categories as $category)
                <li>{{ $category->name }}</li>
            @endforeach
        </ul>
    </div>

    <div>
        <h2>Instructores</h2>
        <ul>
            @foreach($course->instructors as $instructor)
                <li>{{ $instructor->first_name }} {{ $instructor->last_name }}</li>
            @endforeach
        </ul>
    </div>

    <div>
        <h2>Ofertas del Curso</h2>
        <p>Total: {{ $course->course_offerings_count }}</p>
    </div>

    <div>
        <a href="{{ route('dataanalyst.courses.index') }}">Volver al Listado</a>
    </div>
</body>
</html>