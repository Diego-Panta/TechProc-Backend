<!DOCTYPE html>
<html>
<head>
    <title>Editar Alerta</title>
</head>
<body>
    <h1>Editar Alerta</h1>

    <div>
        <a href="{{ route('developer-web.alerts.index') }}">← Volver al Listado</a>
        <a href="{{ route('developer-web.alerts.show', $alert->id) }}">Ver Detalles</a>
    </div>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <!-- Información de la alerta -->
    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0;">
        <strong>ID:</strong> {{ $alert->id_alert ?? $alert->id }} | 
        <strong>Creado:</strong> {{ $alert->created_date->format('d/m/Y H:i') }} | 
        <strong>Por:</strong> {{ $alert->creator->full_name ?? 'N/A' }}
    </div>

    <form method="POST" action="{{ route('developer-web.alerts.update', $alert->id) }}">
        @csrf
        @method('PUT')

        <div>
            <label for="message">Mensaje *</label>
            <textarea id="message" name="message" required>{{ old('message', $alert->message) }}</textarea>
            @error('message') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="type">Tipo *</label>
            <select id="type" name="type" required>
                <option value="info" {{ (old('type', $alert->type) == 'info') ? 'selected' : '' }}>Información</option>
                <option value="warning" {{ (old('type', $alert->type) == 'warning') ? 'selected' : '' }}>Advertencia</option>
                <option value="error" {{ (old('type', $alert->type) == 'error') ? 'selected' : '' }}>Error</option>
                <option value="success" {{ (old('type', $alert->type) == 'success') ? 'selected' : '' }}>Éxito</option>
                <option value="maintenance" {{ (old('type', $alert->type) == 'maintenance') ? 'selected' : '' }}>Mantenimiento</option>
            </select>
            @error('type') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="status">Estado *</label>
            <select id="status" name="status" required>
                <option value="active" {{ (old('status', $alert->status) == 'active') ? 'selected' : '' }}>Activa</option>
                <option value="inactive" {{ (old('status', $alert->status) == 'inactive') ? 'selected' : '' }}>Inactiva</option>
            </select>
            @error('status') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="priority">Prioridad *</label>
            <select id="priority" name="priority" required>
                <option value="1" {{ (old('priority', $alert->priority) == '1') ? 'selected' : '' }}>1 - Baja</option>
                <option value="2" {{ (old('priority', $alert->priority) == '2') ? 'selected' : '' }}>2 - Media</option>
                <option value="3" {{ (old('priority', $alert->priority) == '3') ? 'selected' : '' }}>3 - Alta</option>
                <option value="4" {{ (old('priority', $alert->priority) == '4') ? 'selected' : '' }}>4 - Crítica</option>
                <option value="5" {{ (old('priority', $alert->priority) == '5') ? 'selected' : '' }}>5 - Emergencia</option>
            </select>
            @error('priority') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="link_url">URL de Enlace</label>
            <input type="url" id="link_url" name="link_url" value="{{ old('link_url', $alert->link_url) }}">
            @error('link_url') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="link_text">Texto del Enlace</label>
            <input type="text" id="link_text" name="link_text" value="{{ old('link_text', $alert->link_text) }}">
            @error('link_text') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="start_date">Fecha de Inicio *</label>
            <input type="datetime-local" id="start_date" name="start_date" 
                   value="{{ old('start_date', $alert->start_date->format('Y-m-d\TH:i')) }}" required>
            @error('start_date') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="end_date">Fecha de Fin *</label>
            <input type="datetime-local" id="end_date" name="end_date" 
                   value="{{ old('end_date', $alert->end_date->format('Y-m-d\TH:i')) }}" required>
            @error('end_date') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <button type="submit">Actualizar Alerta</button>
            <a href="{{ route('developer-web.alerts.index') }}">Cancelar</a>
            <button type="button" onclick="deleteAlert()">Eliminar Alerta</button>
        </div>
    </form>

    <script>
        // Validación de fechas
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(this.value);
            
            if (endDate <= startDate) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        });

        // Eliminar alerta
        function deleteAlert() {
            if (confirm('¿Estás seguro de eliminar esta alerta? Esta acción no se puede deshacer.')) {
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