<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Tickets - DataAnalyst</title>
</head>
<body>
    <h1>Reporte de Tickets</h1>
    
    <!-- Filtros -->
    <form method="GET" action="{{ route('dataanalyst.tickets.index') }}">
        <div>
            <label>Fecha Inicio:</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>
        
        <div>
            <label>Fecha Fin:</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>
        
        <div>
            <label>Categoría:</label>
            <input type="text" name="category" value="{{ request('category') }}" placeholder="Filtrar por categoría">
        </div>
        
        <div>
            <label>Prioridad:</label>
            <select name="priority">
                <option value="">Todas</option>
                <option value="baja" {{ request('priority') == 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="media" {{ request('priority') == 'media' ? 'selected' : '' }}>Media</option>
                <option value="alta" {{ request('priority') == 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="critica" {{ request('priority') == 'critica' ? 'selected' : '' }}>Crítica</option>
            </select>
        </div>
        
        <button type="submit">Filtrar</button>
        <button type="button" onclick="window.location.href='{{ route('dataanalyst.tickets.index') }}'">Limpiar</button>
    </form>

    <!-- Estadísticas rápidas -->
    <div>
        <h2>Resumen</h2>
        <p>Total de tickets: {{ $tickets->total() }}</p>
        <p>Mostrando: {{ $tickets->count() }} tickets</p>
    </div>

    <!-- Tabla de tickets -->
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Categoría</th>
                <th>Técnico Asignado</th>
                <th>Fecha Creación</th>
                <th>Fecha Resolución</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_id ?? $ticket->id }}</td>
                <td>{{ $ticket->title }}</td>
                <td>{{ $ticket->status }}</td>
                <td>{{ $ticket->priority }}</td>
                <td>{{ $ticket->category ?? 'N/A' }}</td>
                <td>{{ $ticket->assignedTechnician->user->name ?? 'No asignado' }}</td>
                <td>{{ $ticket->creation_date->format('d/m/Y H:i') }}</td>
                <td>{{ $ticket->resolution_date ? $ticket->resolution_date->format('d/m/Y H:i') : 'Pendiente' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">No se encontraron tickets</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Paginación -->
    <div>
        {{ $tickets->links() }}
    </div>

    <!-- Enlace a estadísticas API -->
    <div>
        <a href="{{ route('dataanalyst.tickets.statistics', request()->query()) }}" target="_blank">
            Ver Estadísticas Completas (JSON)
        </a>
    </div>
</body>
</html>