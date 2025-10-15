<!DOCTYPE html>
<html>
<head>
    <title>Anuncios - Incadev</title>
    <style>
        .announcement-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .announcement-title {
            font-size: 1.5em;
            margin: 0;
            color: #333;
        }
        .announcement-meta {
            color: #666;
            font-size: 0.9em;
        }
        .announcement-views {
            background: #007bff;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .announcement-content {
            margin: 15px 0;
            line-height: 1.6;
        }
        .announcement-button {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .announcement-image {
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
        <h1>Anuncios Importantes</h1>
        
        @if($announcements && $announcements->count() > 0)
            @foreach($announcements as $announcement)
            <div class="announcement-card">
                <div class="announcement-header">
                    <h2 class="announcement-title">{{ $announcement->title }}</h2>
                    <span class="announcement-views">{{ $announcement->views }} vistas</span>
                </div>
                
                <div class="announcement-meta">
                    <strong>Tipo:</strong> {{ ucfirst($announcement->display_type) }} | 
                    <strong>Publicado:</strong> {{ $announcement->created_date->format('d/m/Y') }} |
                    <strong>Válido hasta:</strong> {{ $announcement->end_date->format('d/m/Y') }}
                </div>
                
                @if($announcement->image_url)
                <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}" class="announcement-image">
                @endif
                
                <div class="announcement-content">
                    {{ $announcement->content }}
                </div>
                
                @if($announcement->link_url && $announcement->button_text)
                <a href="{{ $announcement->link_url }}" class="announcement-button" target="_blank">
                    {{ $announcement->button_text }}
                </a>
                @endif
                
                <div style="margin-top: 15px;">
                    <a href="{{ route('public.announcements.show', $announcement->id) }}">Ver detalles completos →</a>
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state">
                <h3>No hay anuncios activos en este momento</h3>
                <p>Vuelve más tarde para ver las últimas novedades.</p>
            </div>
        @endif

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ route('developer-web.announcements.index') }}" style="color: #007bff;">
                ← Panel de Administración de Anuncios
            </a>
        </div>
    </div>
</body>
</html>