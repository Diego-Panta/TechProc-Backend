<!DOCTYPE html>
<html>
<head>
    <title>Detalles de Noticia</title>
    <style>
        .news-detail {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .news-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .news-title {
            font-size: 2em;
            margin: 0;
            color: #333;
        }
        .news-meta {
            color: #666;
            margin: 10px 0;
        }
        .news-views {
            background: #007bff;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .news-content {
            font-size: 1.1em;
            line-height: 1.7;
            margin: 25px 0;
        }
        .news-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .tag {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
        }
        .action-buttons {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="news-detail">
        <div class="action-buttons">
            <a href="{{ route('developer-web.news.index') }}" class="btn btn-secondary">← Volver al Listado</a>
            <a href="{{ route('developer-web.news.edit', $news->id) }}" class="btn btn-primary">Editar Noticia</a>
            <button onclick="deleteNews()" class="btn btn-danger">Eliminar Noticia</button>
        </div>

        <div class="news-header">
            <h1 class="news-title">{{ $news->title }}</h1>
            
            <div class="news-meta">
                <strong>Slug:</strong> {{ $news->slug }} | 
                <strong>Estado:</strong> 
                <span style="padding: 2px 8px; border-radius: 4px; 
                    @if($news->status == 'published') background: #d1ecf1; color: #0c5460; 
                    @elseif($news->status == 'draft') background: #fff3cd; color: #856404; 
                    @else background: #e2e3e5; color: #383d41; @endif">
                    {{ $news->status }}
                </span> | 
                <span class="news-views">{{ $news->views }} vistas</span>
            </div>
            
            <div class="news-meta">
                <strong>Categoría:</strong> {{ $news->category }} | 
                <strong>Publicación:</strong> {{ $news->published_date ? $news->published_date->format('d/m/Y H:i') : 'No publicada' }} | 
                <strong>Autor:</strong> {{ $news->author->full_name ?? 'N/A' }}
            </div>

            <div class="news-meta">
                <strong>Creado:</strong> {{ $news->created_date->format('d/m/Y H:i') }} | 
                <strong>Actualizado:</strong> {{ $news->updated_date->format('d/m/Y H:i') }}
            </div>

            @if($news->tags && count($news->tags) > 0)
            <div class="news-meta">
                <strong>Etiquetas:</strong>
                @foreach($news->tags as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </div>
            @endif
        </div>
        
        @if($news->featured_image)
        <img src="{{ $news->featured_image }}" alt="{{ $news->title }}" class="news-image">
        @endif
        
        <div class="news-content">
            <h3>Resumen</h3>
            <p>{{ $news->summary }}</p>
            
            <h3>Contenido Completo</h3>
            {!! nl2br(e($news->content)) !!}
        </div>

        @if($news->seo_title || $news->seo_description)
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h3>Información SEO</h3>
            @if($news->seo_title)
                <p><strong>Título SEO:</strong> {{ $news->seo_title }}</p>
            @endif
            @if($news->seo_description)
                <p><strong>Descripción SEO:</strong> {{ $news->seo_description }}</p>
            @endif
        </div>
        @endif

        <div class="action-buttons" style="margin-top: 30px;">
            <a href="{{ route('public.news.show', $news->id) }}" target="_blank" class="btn btn-primary">
                Ver en Sitio Público
            </a>
            <a href="{{ route('public.news.show-by-slug', $news->slug) }}" target="_blank" class="btn btn-primary">
                Ver por Slug
            </a>
            <button onclick="resetViews()" class="btn btn-secondary">Resetear Vistas</button>
        </div>
    </div>

    <script>
        function deleteNews() {
            if (confirm('¿Estás seguro de eliminar esta noticia?')) {
                fetch('{{ route("developer-web.news.destroy", $news->id) }}', {
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
                        window.location.href = '{{ route("developer-web.news.index") }}';
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

        function resetViews() {
            if (confirm('¿Resetear contador de vistas a 0?')) {
                fetch('{{ route("developer-web.news.reset-views", $news->id) }}', {
                    method: 'POST',
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
                    alert('Error al resetear vistas');
                });
            }
        }
    </script>
</body>
</html>