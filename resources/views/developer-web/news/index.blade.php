<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Noticias</title>
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
        .news-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .tag {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
        }
    </style>
</head>
<body>
    <h1>Gestión de Noticias</h1>

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
        <a href="{{ route('developer-web.news.create') }}" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
            + Crear Nueva Noticia
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

            <select name="category" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                @foreach(array_keys($categoryCounts) as $category)
                    <option value="{{ $category }}" {{ $filters['category'] == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="search" placeholder="Buscar..." value="{{ $filters['search'] ?? '' }}">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <!-- Estadísticas -->
    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 20px;">
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
            {{ $news->sum('views') }}
        </div>
        <div style="text-align: center; padding: 10px; background: #28a745; color: white; border-radius: 5px;">
            <strong>Categorías</strong><br>
            {{ count($categoryCounts) }}
        </div>
    </div>

    <!-- Lista de noticias -->
    @foreach($news as $item)
    <div class="news-card">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="flex: 1;">
                <h3 style="margin: 0 0 10px 0;">{{ $item->title }}</h3>
                <p style="margin: 0 0 10px 0; color: #666;">{{ $item->summary }}</p>
                
                <div style="margin-bottom: 10px;">
                    @if($item->tags && is_array($item->tags))
                        @foreach(array_slice($item->tags, 0, 3) as $tag)
                            <span class="tag">{{ $tag }}</span>
                        @endforeach
                        @if(count($item->tags) > 3)
                            <span class="tag">+{{ count($item->tags) - 3 }}</span>
                        @endif
                    @endif
                </div>
                
                <div>
                    <strong>Categoría:</strong> {{ $item->category }} | 
                    <strong>Vistas:</strong> {{ $item->views }} | 
                    <strong>Estado:</strong>
                    <span class="status-badge status-{{ $item->status }}">
                        {{ $item->status }}
                    </span>
                </div>
                <div>
                    <strong>Slug:</strong> {{ $item->slug }} | 
                    <strong>Publicación:</strong> {{ $item->published_date ? $item->published_date->format('d/m/Y H:i') : 'No publicada' }}
                </div>
                <div>
                    <strong>Autor:</strong> {{ $item->author->full_name ?? 'N/A' }} | 
                    <strong>Creado:</strong> {{ $item->created_date->format('d/m/Y H:i') }}
                </div>
            </div>
            <div style="margin-left: 20px; display: flex; flex-direction: column; gap: 5px;">
                <a href="{{ route('developer-web.news.show', $item->id) }}">Ver</a>
                <a href="{{ route('developer-web.news.edit', $item->id) }}">Editar</a>
                <button onclick="deleteNews({{ $item->id }})" style="color: red; border: none; background: none; cursor: pointer; text-align: left; padding: 0;">Eliminar</button>
            </div>
        </div>
    </div>
    @endforeach

    {{ $news->links() }}

    <script>
        function deleteNews(id) {
            if (confirm('¿Estás seguro de eliminar esta noticia?')) {
                fetch(`/developer-web/news/${id}`, {
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
                    alert('Error al eliminar la noticia');
                });
            }
        }
    </script>
</body>
</html>