<!DOCTYPE html>
<html>
<head>
    <title>Alertas de Alta Prioridad - Incadev</title>
</head>
<body>
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1>Alertas de Alta Prioridad</h1>
        
        @if($alerts && $alerts->count() > 0)
            @foreach($alerts as $alert)
            <div style="border: 2px solid #dc3545; border-radius: 8px; padding: 20px; margin: 15px 0; 
                background: #fff5f5;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3 style="margin: 0; color: #dc3545;">
                        ⚠️ {{ $alert->message }}
                    </h3>
                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8em; 
                        background: #dc3545; color: white;">
                        @if($alert->priority == 5) EMERGENCIA
                        @elseif($alert->priority == 4) CRÍTICA
                        @else ALTA
                        @endif
                    </span>
                </div>
                
                <div style="color: #666; margin-bottom: 15px;">
                    <strong>Tipo:</strong> {{ ucfirst($alert->type) }} | 
                    <strong>Publicado:</strong> {{ $alert->created_date->format('d/m/Y H:i') }} |
                    <strong>Válido hasta:</strong> {{ $alert->end_date->format('d/m/Y H:i') }}
                </div>
                
                @if($alert->link_url && $alert->link_text)
                <div style="margin-top: 15px;">
                    <a href="{{ $alert->link_url }}" style="display: inline-block; padding: 10px 20px; 
                        background: #dc3545; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                        🚨 {{ $alert->link_text }}
                    </a>
                </div>
                @endif
                
                <div style="margin-top: 15px; font-size: 0.9em; color: #888;">
                    <strong>Creado por:</strong> {{ $alert->creator->full_name ?? 'Sistema' }}
                </div>
            </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 40px; color: #28a745;">
                <h3>✅ No hay alertas de alta prioridad</h3>
                <p>El sistema está funcionando correctamente.</p>
            </div>
        @endif

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ route('public.alerts.index') }}" style="color: #007bff; margin-right: 15px;">
                ← Volver a todas las alertas
            </a>
            <a href="{{ route('developer-web.alerts.index') }}" style="color: #6c757d;">
                Panel de Administración
            </a>
        </div>
    </div>
</body>
</html>