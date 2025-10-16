<!DOCTYPE html>
<html>
<head>
    <title>Consultas de Contacto</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f7fa;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 14px;
            color: #495057;
        }

        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out;
        }

        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .filter-actions {
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            transform: translateY(-1px);
        }

        .contact-item {
            background: white;
            border: 1px solid #e9ecef;
            margin-bottom: 16px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: box-shadow 0.15s ease-in-out;
        }

        .contact-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .contact-name {
            font-weight: 600;
            font-size: 16px;
            color: #2c3e50;
        }

        .contact-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { 
            background-color: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        .status-in_progress { 
            background-color: #cce7ff; 
            color: #004085; 
            border: 1px solid #b3d7ff;
        }
        .status-responded { 
            background-color: #d1ecf1; 
            color: #0c5460; 
            border: 1px solid #b6effb;
        }
        .status-spam { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5b7b1;
        }

        .contact-email {
            color: #6c757d;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .contact-subject {
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
        }

        .contact-message {
            color: #555;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .contact-response {
            background-color: #f8f9fa;
            padding: 12px;
            border-left: 3px solid #007bff;
            margin-bottom: 12px;
            border-radius: 0 4px 4px 0;
        }

        .contact-meta {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .contact-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .contact-actions a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            padding: 6px 12px;
            border: 1px solid #007bff;
            border-radius: 4px;
            transition: all 0.15s ease-in-out;
        }

        .contact-actions a:hover {
            background-color: #007bff;
            color: white;
        }

        .contact-actions form {
            margin: 0;
        }

        .contact-actions button {
            padding: 6px 12px;
            border: 1px solid;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .contact-actions button[style*="#dc3545"] {
            background-color: transparent;
            color: #dc3545;
            border-color: #dc3545;
        }

        .contact-actions button[style*="#dc3545"]:hover {
            background-color: #dc3545;
            color: white;
        }

        .contact-actions button[style*="#28a745"] {
            background-color: transparent;
            color: #28a745;
            border-color: #28a745;
        }

        .contact-actions button[style*="#28a745"]:hover {
            background-color: #28a745;
            color: white;
        }

        .pagination {
            margin-top: 25px;
            display: flex;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <h1>📩 Consultas de Contacto</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filtros avanzados -->
    <div class="filter-section">
        <form method="GET" action="{{ route('developer-web.contact-forms.index') }}">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="status">📊 Estado</label>
                    <select name="status" id="status">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $filters['status'] == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="form_type">📝 Tipo</label>
                    <select name="form_type" id="form_type">
                        <option value="">Todos los tipos</option>
                        @foreach($formTypes as $type)
                            <option value="{{ $type }}" {{ $filters['form_type'] == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="assigned_to">👤 Asignado a</label>
                    <select name="assigned_to" id="assigned_to">
                        <option value="">Todos los asignados</option>
                        @foreach($assignedEmployees as $id => $name)
                            <option value="{{ $id }}" {{ $filters['assigned_to'] == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">🔍 Aplicar Filtros</button>
                <a href="{{ route('developer-web.contact-forms.index') }}" class="btn btn-secondary">🔄 Limpiar</a>
            </div>
        </form>
    </div>

    @if($contactForms->count() > 0)
        @foreach($contactForms as $contact)
        <div class="contact-item">
            <div class="contact-header">
                <div class="contact-name">👤 {{ $contact->full_name }}</div>
                <div class="contact-status status-{{ $contact->status }}">
                    @if($contact->status === 'pending')
                        ⏳ Pendiente
                    @elseif($contact->status === 'in_progress')
                        🔄 En Progreso
                    @elseif($contact->status === 'responded')
                        ✅ Respondido
                    @elseif($contact->status === 'spam')
                        🚫 Spam
                    @else
                        {{ $contact->status }}
                    @endif
                </div>
            </div>

            <div class="contact-email">📧 {{ $contact->email }}</div>

            <div class="contact-subject">📌 {{ $contact->subject }}</div>

            <div class="contact-message">
                {{ Str::limit($contact->message, 150) }}
                @if(strlen($contact->message) > 150)
                    <a href="{{ route('developer-web.contact-forms.show', $contact->id) }}">Ver más</a>
                @endif
            </div>

            @if($contact->response)
            <div class="contact-response">
                <strong>💬 Respuesta enviada:</strong><br>
                {{ Str::limit($contact->response, 200) }}
                @if(strlen($contact->response) > 200)
                    <a href="{{ route('developer-web.contact-forms.show', $contact->id) }}">Ver respuesta completa</a>
                @endif
            </div>
            @endif

            <div class="contact-meta">
                <strong>📅 Recibido:</strong> {{ $contact->submission_date->format('d M Y, h:i A') }}
                <br><strong>📋 Tipo:</strong> {{ $contact->form_type ?? 'General' }}
                
                @if($contact->response_date)
                    <br><strong>✅ Respondido:</strong> {{ $contact->response_date->format('d M Y, h:i A') }}
                    @if($contact->assignedTo && $contact->assignedTo->user)
                        • Por {{ $contact->assignedTo->user->full_name ?? $contact->assignedTo->user->first_name . ' ' . $contact->assignedTo->user->last_name }}
                    @endif
                @endif
                
                @if($contact->assignedTo && $contact->assignedTo->user)
                    <br><strong>👤 Asignado a:</strong> {{ $contact->assignedTo->user->full_name ?? $contact->assignedTo->user->first_name . ' ' . $contact->assignedTo->user->last_name }}
                @endif
            </div>

            <div class="contact-actions">
                <a href="{{ route('developer-web.contact-forms.show', $contact->id) }}">👀 Ver detalles</a>
                
                @if($contact->status !== 'spam')
                <form method="POST" action="{{ route('developer-web.contact-forms.mark-spam', $contact->id) }}">
                    @csrf
                    <button type="submit">🚫 Marcar spam</button>
                </form>
                @endif

                @if($contact->status === 'pending' || $contact->status === 'in_progress')
                <form method="POST" action="{{ route('developer-web.contact-forms.respond', $contact->id) }}">
                    @csrf
                    <button type="submit">✉️ Responder</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach

        <!-- Paginación con filtros -->
        <div class="pagination">
            {{ $contactForms->appends($filters)->links() }}
        </div>

    @else
        <div class="empty-state">
            <div>📭</div>
            <h3>No hay consultas de contacto</h3>
            <p>
                @if($filters['status'] !== 'all' || $filters['form_type'] || $filters['assigned_to'])
                    con los filtros aplicados
                @else
                    en este momento
                @endif
            </p>
        </div>
    @endif
</body>
</html>