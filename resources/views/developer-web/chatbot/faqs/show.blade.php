<!DOCTYPE html>
<html>
<head>
    <title>Detalles de FAQ</title>
    <style>
        .detail-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .faq-detail {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .faq-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .faq-title {
            font-size: 1.8em;
            margin: 0;
            color: #333;
        }
        .faq-meta {
            color: #666;
            margin: 10px 0;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-inactive {
            background: #e2e3e5;
            color: #383d41;
        }
        .keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 15px 0;
        }
        .keyword {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .faq-content {
            margin: 20px 0;
        }
        .answer-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
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
    <div class="detail-container">
        <h1>Detalles de FAQ</h1>

        <div style="margin-bottom: 20px;">
            <a href="{{ route('developer-web.chatbot.faqs.index') }}" class="btn btn-secondary">
                ← Volver al Listado
            </a>
            <a href="{{ route('developer-web.chatbot.faqs.edit', $faq->id) }}" class="btn btn-primary" style="margin-left: 10px;">
                Editar FAQ
            </a>
        </div>

        <div class="faq-detail">
            <div class="faq-header">
                <h2 class="faq-title">{{ $faq->question }}</h2>
                
                <div class="faq-meta">
                    <strong>Categoría:</strong> {{ $faq->category }} | 
                    <strong>Estado:</strong>
                    <span class="status-badge status-{{ $faq->active ? 'active' : 'inactive' }}">
                        {{ $faq->active ? 'Activa' : 'Inactiva' }}
                    </span> |
                    <strong>Usos:</strong> {{ $faq->usage_count }}
                </div>
                
                <div class="faq-meta">
                    <strong>Creado:</strong> {{ $faq->created_date->format('d/m/Y H:i') }} | 
                    <strong>Actualizado:</strong> {{ $faq->updated_date->format('d/m/Y H:i') }}
                </div>

                @if($faq->keywords && count($faq->keywords) > 0)
                <div class="keywords">
                    <strong>Palabras clave:</strong>
                    @foreach($faq->keywords as $keyword)
                    <span class="keyword">{{ $keyword }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="faq-content">
                <h3>Respuesta del Chatbot:</h3>
                <div class="answer-box">
                    {{ $faq->answer }}
                </div>
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <h3>Información Técnica</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    <div><strong>ID Interno:</strong> {{ $faq->id }}</div>
                    <div><strong>ID FAQ:</strong> {{ $faq->id_faq }}</div>
                    <div><strong>Activa:</strong> {{ $faq->active ? 'Sí' : 'No' }}</div>
                    <div><strong>Total de Usos:</strong> {{ $faq->usage_count }}</div>
                </div>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('developer-web.chatbot.faqs.edit', $faq->id) }}" class="btn btn-primary">
                Editar FAQ
            </a>
            <button onclick="deleteFaq()" class="btn btn-danger" style="margin-left: 10px;">
                Eliminar FAQ
            </button>
        </div>
    </div>

    <script>
        function deleteFaq() {
            if (confirm('¿Estás seguro de eliminar esta FAQ? Esta acción no se puede deshacer.')) {
                fetch('{{ route("developer-web.chatbot.faqs.destroy", $faq->id) }}', {
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
                        window.location.href = '{{ route("developer-web.chatbot.faqs.index") }}';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la FAQ');
                });
            }
        }
    </script>
</body>
</html>