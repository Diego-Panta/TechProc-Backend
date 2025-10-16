<!DOCTYPE html>
<html>
<head>
    <title>Noticias - Incadev</title>
    <style>
        .news-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .news-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .news-title {
            font-size: 1.5em;
            margin: 0;
            color: #333;
        }
        .news-meta {
            color: #666;
            font-size: 0.9em;
        }
        .news-views {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .news-summary {
            margin: 15px 0;
            line-height: 1.6;
            color: #555;
        }
        .news-tags {
            margin: 10px 0;
        }
        .tag {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin: 2px;
        }
        .read-more {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .news-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 10px 0;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1>Últimas Noticias</h1>
        
        @if($news && $news->count() > 0)
            @foreach($news as $item)
            <div class="news-card">
                <div class="news-header">
                    <h2 class="news-title">{{ $item->title }}</h2>
                    <span class="news-views">{{ $item->views }} vistas</span>
                </div>
                
                <div class="news-meta">
                    <strong>Categoría:</strong> {{ $item->category }} | 
                    <strong>Publicado:</strong> {{ $item->published_date->format('d/m/Y') }} | 
                    <strong>Por:</strong> {{ $item->author->full_name ?? 'Incadev' }}
                </div>
                
                @if($item->featured_image)
                <img src="{{ $item->featured_image }}" alt="{{ $item->title }}" class="news-image">
                @endif
                
                <div class="news-summary">
                    {{ $item->summary }}
                </div>
                
                @if($item->tags && count($item->tags) > 0)
                <div class="news-tags">
                    @foreach(array_slice($item->tags, 0, 5) as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
                
                <div>
                    <a href="{{ route('public.news.show', $item->id) }}" class="read-more">
                        Leer más →
                    </a>
                    <a href="{{ route('public.news.show-by-slug', $item->slug) }}" class="read-more" style="background: #6c757d; margin-left: 10px;">
                        Ver por URL
                    </a>
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state">
                <h3>No hay noticias disponibles en este momento</h3>
                <p>Vuelve más tarde para ver las últimas novedades.</p>
            </div>
        @endif

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ route('developer-web.news.index') }}" style="color: #007bff;">
                ← Panel de Administración de Noticias
            </a>
        </div>
    </div>
</body>
</html>