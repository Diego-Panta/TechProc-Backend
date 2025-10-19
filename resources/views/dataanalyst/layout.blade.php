<!DOCTYPE html>
<html>
<head>
    <title>Data Analyst - @yield('title')</title>
    <meta charset="utf-8">
</head>
<body>
    <header>
        <h1>Módulo Analista de Datos</h1>
        <nav>
            <a href="{{ route('dataanalyst.students.index') }}">Reporte de Estudiantes</a>
            <a href="{{ route('dataanalyst.courses.index') }}">Reporte de Cursos</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>Sistema de Reportes - Data Analyst</p>
    </footer>
</body>
</html>