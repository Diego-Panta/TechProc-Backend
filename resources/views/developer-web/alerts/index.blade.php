<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Alertas</title>
</head>
<body>
    <h1>Gestión de Alertas</h1>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <div>
        <a href="{{ route('developer-web.alerts.create') }}">+ Crear Nueva Alerta</a>
    </div>

    <!-- Filtros -->
    <div>
        <strong>Filtros:</strong>
        <form method="GET">
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="active" {{ $filters['status'] == 'active' ? 'selected' : '' }}>Activas</option>
                <option value="inactive" {{ $filters['status'] == 'inactive' ? 'selected' : '' }}>Inactivas</option>
            </select>

            <select name="type" onchange="this.form.submit()">
                <option value="">Todos los tipos</option>
                <option value="info" {{ $filters['type'] == 'info' ? 'selected' : '' }}>Info</option>
                <option value="warning" {{ $filters['type'] == 'warning' ? 'selected' : '' }}>Advertencia</option>
                <option value="error" {{ $filters['type'] == 'error' ? 'selected' : '' }}>Error</option>
                <option value="success" {{ $filters['type'] == 'success' ? 'selected' : '' }}>Éxito</option>
                <option value="maintenance" {{ $filters['type'] == 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
            </select>
        </form>
    </div>

    <!-- Estadísticas -->
    <div>
        <div>Total: {{ array_sum($statusCounts) }}</div>
        <div>Activas: {{ $statusCounts['active'] ?? 0 }}</div>
        <div>Inactivas: {{ $statusCounts['inactive'] ?? 0 }}</div>
    </div>

    <!-- Lista de alertas -->
    @foreach($alerts as $alert)
        <div style="border: 1px solid #ccc; padding: 15px; margin: 10px 0;">
            <h3>{{ $alert->message }}</h3>
            <p><strong>Tipo:</strong> {{ $alert->type }} | <strong>Prioridad:</strong> {{ $alert->priority }} | <strong>Estado:</strong> {{ $alert->status }}</p>
            <p><strong>Vigencia:</strong> {{ $alert->start_date->format('d/m/Y H:i') }} - {{ $alert->end_date->format('d/m/Y H:i') }}</p>
            <p><strong>Creado por:</strong> {{ $alert->creator->full_name ?? 'N/A' }} el {{ $alert->created_date->format('d/m/Y H:i') }}</p>
            
            @if($alert->link_url)
                <p><strong>Enlace:</strong> <a href="{{ $alert->link_url }}">{{ $alert->link_text ?? $alert->link_url }}</a></p>
            @endif

            <div>
                <a href="{{ route('developer-web.alerts.show', $alert->id) }}">Ver</a>
                <a href="{{ route('developer-web.alerts.edit', $alert->id) }}">Editar</a>
                <button onclick="deleteAlert({{ $alert->id }})">Eliminar</button>
            </div>
        </div>
    @endforeach

    {{ $alerts->links() }}

    <script>
        function deleteAlert(id) {
            if (confirm('¿Estás seguro de eliminar esta alerta?')) {
                fetch(`/developer-web/alerts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la alerta');
                });
            }
        }
    </script>
</body>
</html>