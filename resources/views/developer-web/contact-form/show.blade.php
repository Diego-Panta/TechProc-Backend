<!DOCTYPE html>
<html>
<head>
    <title>Detalles de Consulta</title>
    <style>
        .detail-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
        }
        .response-section {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .meta-info {
            font-size: 14px;
            color: #666;
        }
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Detalles de Consulta #{{ $contactForm->id }}</h1>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('developer-web.contact-forms.index') }}">← Volver al listado</a>
    </div>

    <!-- Mostrar mensajes de error/success -->
    @if(session('error'))
        <div class="error-message">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <div class="detail-section">
        <div class="detail-label">Información del Contacto</div>
        <div><strong>Nombre:</strong> {{ $contactForm->full_name }}</div>
        <div><strong>Email:</strong> {{ $contactForm->email }}</div>
        <div><strong>Teléfono:</strong> {{ $contactForm->phone ?? 'No proporcionado' }}</div>
        <div><strong>Empresa:</strong> {{ $contactForm->company ?? 'No proporcionada' }}</div>
        <div class="meta-info"><strong>Recibido:</strong> {{ $contactForm->submission_date->format('d M Y, h:i A') }}</div>
        <div class="meta-info"><strong>Estado:</strong> 
            <span style="padding: 2px 6px; border-radius: 8px; 
                @if($contactForm->status === 'pending') background-color: #fff3cd; color: #856404; 
                @elseif($contactForm->status === 'responded') background-color: #d1ecf1; color: #0c5460; 
                @elseif($contactForm->status === 'spam') background-color: #f8d7da; color: #721c24; @endif">
                @if($contactForm->status === 'pending')
                    Pendiente
                @elseif($contactForm->status === 'responded')
                    Respondido
                @elseif($contactForm->status === 'spam')
                    Spam
                @else
                    {{ $contactForm->status }}
                @endif
            </span>
        </div>
    </div>

    <div class="detail-section">
        <div class="detail-label">Asunto</div>
        <div>{{ $contactForm->subject }}</div>
    </div>

    <div class="detail-section">
        <div class="detail-label">Mensaje</div>
        <div style="white-space: pre-wrap;">{{ $contactForm->message }}</div>
    </div>

    @if($contactForm->response)
    <div class="detail-section response-section">
        <div class="detail-label">Respuesta Enviada</div>
        <div style="white-space: pre-wrap; margin-bottom: 10px;">{{ $contactForm->response }}</div>
        <div class="meta-info">
            <strong>Respondido:</strong> {{ $contactForm->response_date->format('d M Y, h:i A') }}
            @if($contactForm->assignedTo && $contactForm->assignedTo->user)
                • Por {{ $contactForm->assignedTo->user->full_name ?? $contactForm->assignedTo->user->first_name . ' ' . $contactForm->assignedTo->user->last_name }}
            @endif
        </div>
    </div>
    @endif

    @if($contactForm->assignedTo && $contactForm->assignedTo->user)
    <div class="detail-section">
        <div class="detail-label">Asignación</div>
        <div><strong>Asignado a:</strong> {{ $contactForm->assignedTo->user->full_name ?? $contactForm->assignedTo->user->first_name . ' ' . $contactForm->assignedTo->user->last_name }}</div>
    </div>
    @endif

    <div class="detail-section">
        <div class="detail-label">Acciones</div>
        
        @if($contactForm->status === 'pending')
        <form method="POST" action="{{ route('developer-web.contact-forms.respond', $contactForm->id) }}">
            @csrf
            <div style="margin-bottom: 10px;">
                <label for="response"><strong>Responder:</strong></label><br>
                <textarea name="response" id="response" rows="6" cols="60" required placeholder="Escribe tu respuesta aquí...">{{ old('response') }}</textarea>
                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                    Mínimo 10 caracteres requeridos
                </div>
            </div>
            <button type="submit" style="padding: 8px 16px; background-color: #28a745; color: white; border: none; border-radius: 4px;">Enviar respuesta</button>
        </form>
        @endif

        @if($contactForm->status !== 'spam')
        <form method="POST" action="{{ route('developer-web.contact-forms.mark-spam', $contactForm->id) }}" style="margin-top: 10px;">
            @csrf
            <button type="submit" style="padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 4px;">Marcar como spam</button>
        </form>
        @endif
    </div>

    <!-- Script para validación básica en el cliente -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const textarea = document.getElementById('response');
            
            if (form && textarea) {
                form.addEventListener('submit', function(e) {
                    const response = textarea.value.trim();
                    
                    if (response.length < 10) {
                        e.preventDefault();
                        alert('La respuesta debe tener al menos 10 caracteres.');
                        textarea.focus();
                    }
                });
            }
        });
    </script>
</body>
</html>