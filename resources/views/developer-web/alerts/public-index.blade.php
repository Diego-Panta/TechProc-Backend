<!DOCTYPE html>
<html>
<head>
    <title>Alertas del Sistema - Incadev</title>
</head>
<body>
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1>Alertas del Sistema</h1>
        
        @if($alerts && $alerts->count() > 0)
            @foreach($alerts as $alert)
            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 15px 0; 
                @if($alert->type == 'info') border-left: 5px solid #17a2b8;
                @elseif($alert->type == 'warning') border-left: 5px solid #ffc107;
                @elseif($alert->type == 'error') border-left: 5px solid #dc3545;
                @elseif($alert->type == 'success') border-left: 5px solid #28a745;
                @else border-left: 5px solid #6c757d; @endif">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3 style="margin: 0; color: #333;">{{ $alert->message }}</h3>
                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8em; 
                        @if($alert->priority >= 4) background: #dc3545; color: white;
                        @elseif($alert->priority == 3) background: #fd7e14; color: white;
                        @else background: #6c757d; color: white; @endif">
                        Prioridad {{ $alert->priority }}
                    </span>
                </div>
                
                <div style="color: #666; margin-bottom: 15px;">
                    <strong>Tipo:</strong> {{ ucfirst($alert->type) }} | 
                    <strong>Publicado:</strong> {{ $alert->created_date->format('d/m/Y H:i') }} |
                    <strong>Válido hasta:</strong> {{ $alert->end_date->format('d/m/Y H:i') }}
                </div>
                
                @if($alert->link_url && $alert->link_text)
                <div style="margin-top: 15px;">
                    <a href="{{ $alert->link_url }}" style="display: inline-block; padding: 8px 16px; 
                        background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                        {{ $alert->link_text }}
                    </a>
                </div>
                @endif
                
                <div style="margin-top: 15px; font-size: 0.9em; color: #888;">
                    <strong>Creado por:</strong> {{ $alert->creator->full_name ?? 'Sistema' }}
                </div>
            </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No hay alertas activas en este momento</h3>
                <p>El sistema está funcionando normalmente.</p>
            </div>
        @endif

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ route('public.alerts.high-priority') }}" style="color: #dc3545; margin-right: 15px;">
                Ver Alertas de Alta Prioridad
            </a>
            <a href="{{ route('developer-web.alerts.index') }}" style="color: #007bff;">
                Panel de Administración
            </a>
        </div>
    </div>
</body>
</html>