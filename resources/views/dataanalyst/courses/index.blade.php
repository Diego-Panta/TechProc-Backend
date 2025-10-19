<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Cursos - Data Analyst</title>
</head>

<body>
    <h1>Reporte de Cursos</h1>

    <!-- Filtros -->
    <form method="GET" action="{{ route('dataanalyst.courses.index') }}">
        <div>
            <label>Buscar:</label>
            <input type="text" name="search" value="{{ request('search') }}">
        </div>

        <div>
            <label>Categoría:</label>
            <select name="category_id">
                <option value="">Todas</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Nivel:</label>
            <select name="level">
                <option value="">Todos</option>
                <option value="basic" {{ request('level') == 'basic' ? 'selected' : '' }}>Básico</option>
                <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermedio</option>
                <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Avanzado</option>
            </select>
        </div>

        <div>
            <label>Estado:</label>
            <select name="status">
                <option value="">Todos</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activo</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <button type="submit">Filtrar</button>
    </form>

    <!-- Tabla de cursos -->
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Nivel</th>
                <th>Categorías</th>
                <th>Matrículas</th>
                <th>Ofertas</th>
                <th>Grupos</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
            <tr>
                <td>{{ $course->id }}</td>
                <td>{{ $course->title }}</td>
                <td>{{ $course->level }}</td>
                <td>
                    @foreach($course->categories as $category)
                    {{ $category->name }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>{{ $course->enrollments_count }}</td>
                <td>{{ $course->course_offerings_count }}</td>
                <td>{{ $course->groups_count }}</td>
                <td>${{ number_format($course->selling_price, 2) }}</td>
                <td>{{ $course->status ? 'Activo' : 'Inactivo' }}</td>
                <td>
                    <a href="{{ route('dataanalyst.courses.show', $course->id) }}">Ver Detalle</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación -->
    <div>
        {{ $courses->links() }}
    </div>

    <!-- Enlace a estadísticas -->
    <div>
        <a href="{{ route('dataanalyst.courses.statistics', request()->query()) }}">Ver Estadísticas Completas</a>
    </div>
</body>

</html>