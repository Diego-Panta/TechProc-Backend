<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Anuncios</title>
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-draft {
            background: #fff3cd;
            color: #856404;
        }

        .status-published {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-archived {
            background: #e2e3e5;
            color: #383d41;
        }

        .filter-active {
            font-weight: bold;
            text-decoration: underline;
        }

        .announcement-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h1>Gestión de Anuncios</h1>

    @if(session('success'))
    <div style="color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; margin-bottom: 15px;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin-bottom: 15px;">
        {{ session('error') }}
    </div>
    @endif

    <div style="margin-bottom: 20px;">
        <a href="{{ route('developer-web.announcements.create') }}" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
            + Crear Nuevo Anuncio
        </a>
    </div>

    <!-- Filtros -->
    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <strong>Filtros:</strong>
        <form method="GET" style="display: inline;">
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="draft" {{ $filters['status'] == 'draft' ? 'selected' : '' }}>Borrador</option>
                <option value="published" {{ $filters['status'] == 'published' ? 'selected' : '' }}>Publicado</option>
                <option value="archived" {{ $filters['status'] == 'archived' ? 'selected' : '' }}>Archivado</option>
            </select>

            <select name="display_type" onchange="this.form.submit()">
                <option value="">Todos los tipos</option>
                <option value="banner" {{ $filters['display_type'] == 'banner' ? 'selected' : '' }}>Banner</option>
                <option value="modal" {{ $filters['display_type'] == 'modal' ? 'selected' : '' }}>Modal</option>
                <option value="popup" {{ $filters['display_type'] == 'popup' ? 'selected' : '' }}>Popup</option>
                <option value="notification" {{ $filters['display_type'] == 'notification' ? 'selected' : '' }}>Notificación</option>
            </select>
        </form>
    </div>

    <!-- Estadísticas -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 20px;">
        <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 5px;">
            <strong>Total</strong><br>
            {{ array_sum($statusCounts) }}
        </div>
        <div style="text-align: center; padding: 10px; background: #fff3cd; border-radius: 5px;">
            <strong>Borradores</strong><br>
            {{ $statusCounts['draft'] ?? 0 }}
        </div>
        <div style="text-align: center; padding: 10px; background: #d1ecf1; border-radius: 5px;">
            <strong>Publicados</strong><br>
            {{ $statusCounts['published'] ?? 0 }}
        </div>
        <div style="text-align: center; padding: 10px; background: #e2e3e5; border-radius: 5px;">
            <strong>Archivados</strong><br>
            {{ $statusCounts['archived'] ?? 0 }}
        </div>
        <div style="text-align: center; padding: 10px; background: #007bff; color: white; border-radius: 5px;">
            <strong>Vistas Totales</strong><br>
            {{ $announcements->sum('views') }}
        </div>
    </div>

    <!-- Lista de anuncios -->
    @foreach($announcements as $announcement)
    <div class="announcement-card">
        <div style="display: flex; justify-content: between; align-items: start;">
            <div style="flex: 1;">
                <h3 style="margin: 0 0 10px 0;">{{ $announcement->title }}</h3>
                <p style="margin: 0 0 10px 0;">{{ $announcement->content }}</p>
                <div><strong>Tipo:</strong> {{ $announcement->display_type }} | <strong>Página:</strong> {{ $announcement->target_page }}</div>
                <div><strong>Vistas:</strong> {{ $announcement->views }} | <strong>Estado:</strong>
                    <span class="status-badge status-{{ $announcement->status }}">
                        {{ $announcement->status }}
                    </span>
                </div>
                <div><strong>Vigencia:</strong> {{ $announcement->start_date->format('d/m/Y') }} - {{ $announcement->end_date->format('d/m/Y') }}</div>
                <div><strong>Creado por:</strong> {{ $announcement->creator->full_name ?? 'N/A' }} el {{ $announcement->created_date->format('d/m/Y H:i') }}</div>
            </div>
            <div style="margin-left: 20px;">
                <a href="{{ route('developer-web.announcements.show', $announcement->id) }}">Ver</a>
                <a href="{{ route('developer-web.announcements.edit', $announcement->id) }}">Editar</a>
                <button onclick="deleteAnnouncement({{ $announcement->id }})" style="color: red;">Eliminar</button>
            </div>
        </div>
    </div>
    @endforeach

    {{ $announcements->links() }}

    <script>
        function deleteAnnouncement(id) {
            if (confirm('¿Estás seguro de eliminar este anuncio?')) {
                fetch(`/developer-web/announcements/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
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
                        alert('Error al eliminar el anuncio');
                    });
            }
        }
    </script>
</body>

</html>