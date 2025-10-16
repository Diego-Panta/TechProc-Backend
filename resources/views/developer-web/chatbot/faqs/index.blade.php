<!DOCTYPE html>
<html>

<head>
    <title>Gestión de FAQs - Chatbot</title>
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-active {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-inactive {
            background: #e2e3e5;
            color: #383d41;
        }

        .faq-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background: white;
        }

        .faq-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .faq-actions {
            display: flex;
            gap: 10px;
        }

        .faq-actions a {
            padding: 4px 8px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
        }

        .faq-actions .btn-view {
            background: #17a2b8;
            color: white;
        }

        .faq-actions .btn-edit {
            background: #ffc107;
            color: black;
        }

        .faq-actions .btn-delete {
            background: #dc3545;
            color: white;
        }

        .keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 10px 0;
        }

        .keyword {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
        }

        .filter-active {
            font-weight: bold;
            text-decoration: underline;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h1>Gestión de FAQs del Chatbot</h1>

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
            <a href="{{ route('developer-web.chatbot.faqs.create') }}" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                + Crear Nueva FAQ
            </a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $faqs->total() }}</div>
                <div>Total FAQs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $faqs->where('active', true)->count() }}</div>
                <div>Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $faqs->where('active', false)->count() }}</div>
                <div>Inactivas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $faqs->sum('usage_count') }}</div>
                <div>Usos Totales</div>
            </div>
        </div>

        <!-- Filtros -->
        <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <strong>Filtros:</strong>
            <form method="GET" style="display: inline;">
                <select name="category" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                    <option value="{{ $category }}" {{ $filters['category'] == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                    @endforeach
                </select>

                <select name="active" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ $filters['active'] === '1' ? 'selected' : '' }}>Activas</option>
                    <option value="0" {{ $filters['active'] === '0' ? 'selected' : '' }}>Inactivas</option>
                </select>

                <input type="text" name="search" placeholder="Buscar en preguntas..." value="{{ $filters['search'] ?? '' }}">
                <button type="submit">Buscar</button>
                <a href="{{ route('developer-web.chatbot.faqs.index') }}" style="margin-left: 10px;">Limpiar</a>
            </form>
        </div>

        <!-- Lista de FAQs -->
        @if($faqs->count() > 0)
        @foreach($faqs as $faq)
        <div class="faq-card">
            <div class="faq-header">
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 5px 0;">{{ $faq->question }}</h3>
                    <div>
                        <strong>Categoría:</strong> {{ $faq->category }} |
                        <strong>Estado:</strong>
                        <span class="status-badge status-{{ $faq->active ? 'active' : 'inactive' }}">
                            {{ $faq->active ? 'Activa' : 'Inactiva' }}
                        </span> |
                        <strong>Usos:</strong> {{ $faq->usage_count }}
                    </div>
                    <div><strong>Creado:</strong> {{ $faq->created_date->format('d/m/Y H:i') }}</div>

                    @if($faq->keywords && count($faq->keywords) > 0)
                    <div class="keywords">
                        <strong>Palabras clave:</strong>
                        @foreach($faq->keywords as $keyword)
                        <span class="keyword">{{ $keyword }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="faq-actions">
                    <a href="{{ route('developer-web.chatbot.faqs.show', $faq->id) }}" class="btn-view">Ver</a>
                    <a href="{{ route('developer-web.chatbot.faqs.edit', $faq->id) }}" class="btn-edit">Editar</a>
                    <button onclick="deleteFaq({{ $faq->id }})" class="btn-delete">Eliminar</button>
                </div>
            </div>
            <div style="margin-top: 10px;">
                <strong>Respuesta:</strong>
                <p style="margin: 5px 0 0 0; color: #555;">{{ Str::limit($faq->answer, 200) }}</p>
            </div>
        </div>
        @endforeach

        {{ $faqs->links() }}
        @else
        <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 5px;">
            <h3>No se encontraron FAQs</h3>
            <p>No hay FAQs que coincidan con los filtros aplicados.</p>
            <a href="{{ route('developer-web.chatbot.faqs.create') }}" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                Crear primera FAQ
            </a>
        </div>
        @endif
    </div>

    <script>
        function deleteFaq(id) {
            if (confirm('¿Estás seguro de eliminar esta FAQ? Esta acción no se puede deshacer.')) {
                fetch(`/developer-web/chatbot/faqs/${id}`, {
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
                        alert('Error al eliminar la FAQ');
                    });
            }
        }
    </script>
</body>

</html>