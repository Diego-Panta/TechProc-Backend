<!DOCTYPE html>
<html>
<head>
    <title>Detalles de Alerta</title>
</head>
<body>
    <h1>Detalles de Alerta</h1>

    <div>
        <a href="{{ route('developer-web.alerts.index') }}">← Volver al Listado</a>
        <a href="{{ route('developer-web.alerts.edit', $alert->id) }}">Editar</a>
    </div>

    <div style="border: 1px solid #ccc; padding: 20px; margin: 15px 0;">
        <h2>{{ $alert->message }}</h2>
        
        <div style="margin: 10px 0;">
            <strong>ID:</strong> {{ $alert->id_alert ?? $alert->id }}
        </div>
        
        <div style="margin: 10px 0;">
            <strong>Tipo:</strong> 
            <span style="padding: 4px 8px; border-radius: 4px; 
                @if($alert->type == 'info') background: #d1ecf1; color: #0c5460;
                @elseif($alert->type == 'warning') background: #fff3cd; color: #856404;
                @elseif($alert->type == 'error') background: #f8d7da; color: #721c24;
                @elseif($alert->type == 'success') background: #d4edda; color: #155724;
                @else background: #e2e3e5; color: #383d41; @endif">
                {{ ucfirst($alert->type) }}
            </span>
        </div>

        <div style="margin: 10px 0;">
            <strong>Estado:</strong> 
            <span style="padding: 4px 8px; border-radius: 4px; 
                @if($alert->status == 'active') background: #d4edda; color: #155724;
                @else background: #f8d7da; color: #721c24; @endif">
                {{ $alert->status == 'active' ? 'Activa' : 'Inactiva' }}
            </span>
        </div>

        <div style="margin: 10px 0;">
            <strong>Prioridad:</strong> 
            <span style="padding: 4px 8px; border-radius: 4px; 
                @if($alert->priority == 1) background: #d1ecf1; color: #0c5460;
                @elseif($alert->priority == 2) background: #fff3cd; color: #856404;
                @elseif($alert->priority == 3) background: #ffeaa7; color: #6c5ce7;
                @elseif($alert->priority == 4) background: #fd7e14; color: white;
                @else background: #dc3545; color: white; @endif">
                {{ $alert->priority }} - 
                @if($alert->priority == 1) Baja
                @elseif($alert->priority == 2) Media
                @elseif($alert->priority == 3) Alta
                @elseif($alert->priority == 4) Crítica
                @else Emergencia
                @endif
            </span>
        </div>

        <div style="margin: 10px 0;">
            <strong>Vigencia:</strong> 
            {{ $alert->start_date->format('d/m/Y H:i') }} - {{ $alert->end_date->format('d/m/Y H:i') }}
        </div>

        @if($alert->link_url)
        <div style="margin: 10px 0;">
            <strong>Enlace:</strong> 
            <a href="{{ $alert->link_url }}" target="_blank">
                {{ $alert->link_text ?? $alert->link_url }}
            </a>
        </div>
        @endif

        <div style="margin: 10px 0;">
            <strong>Creado por:</strong> {{ $alert->creator->full_name ?? 'N/A' }}
        </div>

        <div style="margin: 10px 0;">
            <strong>Fecha de creación:</strong> {{ $alert->created_date->format('d/m/Y H:i') }}
        </div>

        <div style="margin: 10px 0;">
            <strong>Tiempo restante:</strong> 
            @php
                $now = now();
                $end = $alert->end_date;
                $diff = $now->diff($end);
            @endphp
            @if($now > $end)
                <span style="color: red;">Expirada</span>
            @elseif($diff->days == 0)
                <span style="color: orange;">Hoy expira</span>
            @else
                <span style="color: green;">{{ $diff->days }} días restantes</span>
            @endif
        </div>
    </div>

    <div>
        <button onclick="deleteAlert()" style="background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 4px;">
            Eliminar Alerta
        </button>
    </div>

    <script>
        function deleteAlert() {
            if (confirm('¿Estás seguro de eliminar esta alerta?')) {
                fetch('{{ route("developer-web.alerts.destroy", $alert->id) }}', {
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
                        window.location.href = '{{ route("developer-web.alerts.index") }}';
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