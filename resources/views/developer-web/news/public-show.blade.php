<!DOCTYPE html>
<html>
<head>
    <title>{{ $news->title }} - Incadev</title>
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
        .related-news {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .related-item {
            margin: 10px 0;
            padding: 10px;
            border-left: 3px solid #007bff;
            background: #f8f9fa;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="news-detail">
        <div class="news-header">
            <h1 class="news-title">{{ $news->title }}</h1>
            
            <div class="news-meta">
                <strong>Categoría:</strong> {{ $news->category }} | 
                <strong>Publicado:</strong> {{ $news->published_date->format('d/m/Y H:i') }} | 
                <strong>Por:</strong> {{ $news->author->full_name ?? 'Incadev' }} |
                <span class="news-views">{{ $news->views }} vistas</span>
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
            {!! nl2br(e($news->content)) !!}
        </div>

        <!-- Noticias relacionadas -->
        @if(isset($relatedNews) && count($relatedNews) > 0)
        <div class="related-news">
            <h3>Noticias Relacionadas</h3>
            @foreach($relatedNews as $related)
            <div class="related-item">
                <h4 style="margin: 0 0 5px 0;">
                    <a href="{{ route('public.news.show', $related['id']) }}" style="color: #333; text-decoration: none;">
                        {{ $related['title'] }}
                    </a>
                </h4>
                <p style="margin: 0; color: #666; font-size: 0.9em;">
                    {{ \Illuminate\Support\Str::limit($related['summary'], 100) }}
                </p>
                <div style="font-size: 0.8em; color: #999; margin-top: 5px;">
                    {{ $related['published_date']->format('d/m/Y') }} • {{ $related['views'] }} vistas
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
        <div>
            <a href="{{ route('public.news.index') }}" class="back-link">← Volver a todas las noticias</a>
            <br>
            <a href="{{ route('developer-web.news.index') }}" class="back-link">← Panel de Administración</a>
        </div>
    </div>

    <script>
        // Opcional: Registrar tiempo de lectura
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.querySelector('.news-content');
            if (content) {
                const text = content.textContent || content.innerText;
                const words = text.split(/\s+/).length;
                const readingTime = Math.ceil(words / 200); // 200 palabras por minuto
                
                console.log(`Tiempo de lectura estimado: ${readingTime} minuto${readingTime !== 1 ? 's' : ''}`);
            }
        });
    </script>
</body>
</html>