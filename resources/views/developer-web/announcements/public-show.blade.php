<!DOCTYPE html>
<html>
<head>
    <title>{{ $announcement->title }} - Incadev</title>
    <style>
        .announcement-detail {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .announcement-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .announcement-title {
            font-size: 2em;
            margin: 0;
            color: #333;
        }
        .announcement-meta {
            color: #666;
            margin: 10px 0;
        }
        .announcement-views {
            background: #007bff;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .announcement-content {
            font-size: 1.1em;
            line-height: 1.7;
            margin: 25px 0;
        }
        .announcement-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .announcement-button {
            display: inline-block;
            padding: 12px 25px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1em;
            margin: 15px 0;
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
    <div class="announcement-detail">
        <div class="announcement-header">
            <h1 class="announcement-title">{{ $announcement->title }}</h1>
            
            <div class="announcement-meta">
                <strong>Tipo:</strong> {{ ucfirst($announcement->display_type) }} | 
                <strong>Publicado:</strong> {{ $announcement->created_date->format('d/m/Y H:i') }} |
                <strong>Válido hasta:</strong> {{ $announcement->end_date->format('d/m/Y H:i') }} |
                <span class="announcement-views">{{ $announcement->views }} vistas</span>
            </div>
            
            @if($announcement->creator)
            <div class="announcement-meta">
                <strong>Creado por:</strong> {{ $announcement->creator->full_name }}
            </div>
            @endif
        </div>
        
        @if($announcement->image_url)
        <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}" class="announcement-image">
        @endif
        
        <div class="announcement-content">
            {!! nl2br(e($announcement->content)) !!}
        </div>
        
        @if($announcement->link_url && $announcement->button_text)
        <div>
            <a href="{{ $announcement->link_url }}" class="announcement-button" target="_blank">
                {{ $announcement->button_text }}
            </a>
        </div>
        @endif
        
        <div>
            <a href="{{ route('public.announcements.index') }}" class="back-link">← Volver a todos los anuncios</a>
            <br>
            <a href="{{ route('developer-web.announcements.index') }}" class="back-link">← Panel de Administración</a>
        </div>
    </div>

    <script>
        // Opcional: Registrar tiempo de lectura
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.querySelector('.announcement-content');
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