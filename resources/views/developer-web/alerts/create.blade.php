<!DOCTYPE html>
<html>
<head>
    <title>Crear Nueva Alerta</title>
</head>
<body>
    <h1>Crear Nueva Alerta</h1>

    <div>
        <a href="{{ route('developer-web.alerts.index') }}">← Volver al Listado</a>
    </div>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('developer-web.alerts.store') }}">
        @csrf

        <div>
            <label for="message">Mensaje *</label>
            <textarea id="message" name="message" required>{{ old('message') }}</textarea>
            @error('message') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="type">Tipo *</label>
            <select id="type" name="type" required>
                <option value="">Seleccionar tipo</option>
                <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Información</option>
                <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Advertencia</option>
                <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Éxito</option>
                <option value="maintenance" {{ old('type') == 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
            </select>
            @error('type') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="status">Estado *</label>
            <select id="status" name="status" required>
                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Activa</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactiva</option>
            </select>
            @error('status') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="priority">Prioridad *</label>
            <select id="priority" name="priority" required>
                <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>1 - Baja</option>
                <option value="2" {{ old('priority') == '2' ? 'selected' : '' }}>2 - Media</option>
                <option value="3" {{ old('priority') == '3' ? 'selected' : '' }}>3 - Alta</option>
                <option value="4" {{ old('priority') == '4' ? 'selected' : '' }}>4 - Crítica</option>
                <option value="5" {{ old('priority') == '5' ? 'selected' : '' }}>5 - Emergencia</option>
            </select>
            @error('priority') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="link_url">URL de Enlace</label>
            <input type="url" id="link_url" name="link_url" value="{{ old('link_url') }}">
            @error('link_url') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="link_text">Texto del Enlace</label>
            <input type="text" id="link_text" name="link_text" value="{{ old('link_text') }}">
            @error('link_text') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="start_date">Fecha de Inicio *</label>
            <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
            @error('start_date') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="end_date">Fecha de Fin *</label>
            <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
            @error('end_date') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <button type="submit">Crear Alerta</button>
            <a href="{{ route('developer-web.alerts.index') }}">Cancelar</a>
        </div>
    </form>

    <script>
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(this.value);
            
            if (endDate <= startDate) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        });
    </script>
</body>
</html>