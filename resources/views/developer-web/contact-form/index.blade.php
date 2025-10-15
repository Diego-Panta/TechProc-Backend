<!DOCTYPE html>
<html>
<head>
    <title>Consultas de Contacto</title>
    <style>
        .contact-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 5px;
        }
        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .contact-name {
            font-weight: bold;
            font-size: 16px;
        }
        .contact-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-responded { background-color: #d1ecf1; color: #0c5460; }
        .status-spam { background-color: #f8d7da; color: #721c24; }
        .contact-email {
            color: #666;
            margin-bottom: 5px;
        }
        .contact-subject {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .contact-message {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        .contact-response {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 3px solid #007bff;
            margin-bottom: 10px;
        }
        .contact-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .contact-actions {
            margin-top: 10px;
        }
        .filter-buttons {
            margin-bottom: 20px;
        }
        .filter-buttons a {
            display: inline-block;
            padding: 8px 16px;
            margin-right: 5px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
        }
        .filter-buttons a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <h1>Consultas de Contacto</h1>

    @if(session('success'))
        <div style="color: green; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="color: red; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtros de estado -->
    <div class="filter-buttons">
        @foreach($statuses as $key => $label)
            <a href="?status={{ $key }}" class="{{ $status == $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($contactForms->count() > 0)
        @foreach($contactForms as $contact)
        <div class="contact-item">
            <div class="contact-header">
                <div class="contact-name">{{ $contact->full_name }}</div>
                <div class="contact-status status-{{ $contact->status }}">
                    @if($contact->status === 'pending')
                        Pendiente
                    @elseif($contact->status === 'responded')
                        Respondido
                    @elseif($contact->status === 'spam')
                        Spam
                    @else
                        {{ $contact->status }}
                    @endif
                </div>
            </div>

            <div class="contact-email">{{ $contact->email }}</div>

            <div class="contact-subject">{{ $contact->subject }}</div>

            <div class="contact-message">
                {{ Str::limit($contact->message, 150) }}
                @if(strlen($contact->message) > 150)
                    <a href="{{ route('developer-web.contact-forms.show', $contact->id) }}">Ver más</a>
                @endif
            </div>

            @if($contact->response)
            <div class="contact-response">
                <strong>Respuesta enviada:</strong><br>
                {{ Str::limit($contact->response, 200) }}
                @if(strlen($contact->response) > 200)
                    <a href="{{ route('developer-web.contact-forms.show', $contact->id) }}">Ver respuesta completa</a>
                @endif
            </div>
            @endif

            <div class="contact-meta">
                <strong>Recibido:</strong> {{ $contact->submission_date->format('d M Y, h:i A') }}
                
                @if($contact->response_date)
                    <br><strong>Respondido:</strong> {{ $contact->response_date->format('d M Y, h:i A') }}
                    @if($contact->assignedTo && $contact->assignedTo->user)
                        • Por {{ $contact->assignedTo->user->full_name ?? $contact->assignedTo->user->first_name . ' ' . $contact->assignedTo->user->last_name }}
                    @endif
                @endif
                
                @if($contact->assignedTo && $contact->assignedTo->user)
                    <br><strong>Asignado a:</strong> {{ $contact->assignedTo->user->full_name ?? $contact->assignedTo->user->first_name . ' ' . $contact->assignedTo->user->last_name }}
                @endif
            </div>

            <div class="contact-actions">
                <a href="{{ route('developer-web.contact-forms.show', $contact->id) }}">Ver detalles completos</a>
                
                @if($contact->status !== 'spam')
                <form method="POST" action="{{ route('developer-web.contact-forms.mark-spam', $contact->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="margin-left: 10px; color: #dc3545;">Marcar como spam</button>
                </form>
                @endif

                @if($contact->status === 'pending')
                <form method="POST" action="{{ route('developer-web.contact-forms.respond', $contact->id) }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="margin-left: 10px; color: #28a745;">Responder</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach

        <!-- Paginación -->
        <div style="margin-top: 20px;">
            {{ $contactForms->links() }}
        </div>

    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            No hay consultas de contacto 
            @if($status !== 'all')
                con estado "{{ $statuses[$status] }}"
            @endif
        </div>
    @endif
</body>
</html>