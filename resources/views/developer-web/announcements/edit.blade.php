<!DOCTYPE html>
<html>
<head>
    <title>Editar Anuncio</title>
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="url"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        .preview-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .announcement-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Editar Anuncio</h1>

        <div style="margin-bottom: 20px;">
            <a href="{{ route('developer-web.announcements.index') }}" class="btn btn-secondary">
                ← Volver al Listado
            </a>
            <a href="{{ route('developer-web.announcements.show', $announcement->id) }}" class="btn btn-secondary" style="margin-left: 10px;">
                Ver Detalles
            </a>
        </div>

        @if(session('success'))
            <div style="color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Información del anuncio -->
        <div class="announcement-info">
            <strong>ID:</strong> {{ $announcement->id_announcement ?? $announcement->id }} | 
            <strong>Vistas:</strong> {{ $announcement->views }} | 
            <strong>Creado:</strong> {{ $announcement->created_date->format('d/m/Y H:i') }} | 
            <strong>Por:</strong> {{ $announcement->creator->full_name ?? 'N/A' }}
        </div>

        <form method="POST" action="{{ route('developer-web.announcements.update', $announcement->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Título del Anuncio *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                @error('title') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="content">Contenido *</label>
                <textarea id="content" name="content" required>{{ old('content', $announcement->content) }}</textarea>
                @error('content') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="display_type">Tipo de Visualización *</label>
                    <select id="display_type" name="display_type" required>
                        <option value="banner" {{ (old('display_type', $announcement->display_type) == 'banner') ? 'selected' : '' }}>Banner</option>
                        <option value="modal" {{ (old('display_type', $announcement->display_type) == 'modal') ? 'selected' : '' }}>Modal</option>
                        <option value="popup" {{ (old('display_type', $announcement->display_type) == 'popup') ? 'selected' : '' }}>Popup</option>
                        <option value="notification" {{ (old('display_type', $announcement->display_type) == 'notification') ? 'selected' : '' }}>Notificación</option>
                    </select>
                    @error('display_type') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="target_page">Página Objetivo *</label>
                    <input type="text" id="target_page" name="target_page" 
                           value="{{ old('target_page', $announcement->target_page) }}" 
                           placeholder="Ej: home, lms, courses, etc." required>
                    @error('target_page') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="image_url">URL de la Imagen</label>
                <input type="url" id="image_url" name="image_url" 
                       value="{{ old('image_url', $announcement->image_url) }}" 
                       placeholder="https://ejemplo.com/imagen.jpg">
                @error('image_url') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="link_url">URL de Enlace</label>
                    <input type="url" id="link_url" name="link_url" 
                           value="{{ old('link_url', $announcement->link_url) }}" 
                           placeholder="https://ejemplo.com/destino">
                    @error('link_url') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="button_text">Texto del Botón</label>
                    <input type="text" id="button_text" name="button_text" 
                           value="{{ old('button_text', $announcement->button_text) }}" 
                           placeholder="Ej: Ver más, Matricularme ahora">
                    @error('button_text') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Estado *</label>
                    <select id="status" name="status" required>
                        <option value="draft" {{ (old('status', $announcement->status) == 'draft') ? 'selected' : '' }}>Borrador</option>
                        <option value="published" {{ (old('status', $announcement->status) == 'published') ? 'selected' : '' }}>Publicado</option>
                        <option value="archived" {{ (old('status', $announcement->status) == 'archived') ? 'selected' : '' }}>Archivado</option>
                    </select>
                    @error('status') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Fecha de Inicio *</label>
                    <input type="datetime-local" id="start_date" name="start_date" 
                           value="{{ old('start_date', $announcement->start_date->format('Y-m-d\TH:i')) }}" required>
                    @error('start_date') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="end_date">Fecha de Fin *</label>
                    <input type="datetime-local" id="end_date" name="end_date" 
                           value="{{ old('end_date', $announcement->end_date->format('Y-m-d\TH:i')) }}" required>
                    @error('end_date') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Vista Previa Dinámica -->
            <div class="preview-section">
                <h3>Vista Previa</h3>
                <div id="preview-content" style="padding: 15px; border: 1px dashed #ccc; border-radius: 5px;">
                    <p><strong id="preview-title">{{ $announcement->title }}</strong></p>
                    <p id="preview-content-text">{{ $announcement->content }}</p>
                    <div id="preview-button" style="{{ $announcement->button_text ? 'display: block;' : 'display: none;' }}">
                        <button type="button" style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 3px;">
                            <span id="preview-button-text">{{ $announcement->button_text ?? 'Botón' }}</span>
                        </button>
                    </div>
                    <p style="font-size: 12px; color: #666; margin-top: 10px;">
                        Tipo: <span id="preview-type">{{ $announcement->display_type }}</span> | 
                        Página: <span id="preview-page">{{ $announcement->target_page }}</span>
                    </p>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn btn-primary">Actualizar Anuncio</button>
                <a href="{{ route('developer-web.announcements.index') }}" class="btn btn-secondary" style="margin-left: 10px;">
                    Cancelar
                </a>
                <button type="button" onclick="deleteAnnouncement()" class="btn btn-danger" style="margin-left: 10px;">
                    Eliminar Anuncio
                </button>
            </div>
        </form>
    </div>

    <script>
        // Actualizar vista previa en tiempo real
        function updatePreview() {
            document.getElementById('preview-title').textContent = 
                document.getElementById('title').value || '[Título del anuncio]';
            
            document.getElementById('preview-content-text').textContent = 
                document.getElementById('content').value || '[Contenido del anuncio]';
            
            const buttonText = document.getElementById('button_text').value;
            const buttonElement = document.getElementById('preview-button');
            const previewButtonText = document.getElementById('preview-button-text');
            
            if (buttonText) {
                buttonElement.style.display = 'block';
                previewButtonText.textContent = buttonText;
            } else {
                buttonElement.style.display = 'none';
            }
            
            document.getElementById('preview-type').textContent = 
                document.getElementById('display_type').value || '[Tipo]';
            
            document.getElementById('preview-page').textContent = 
                document.getElementById('target_page').value || '[Página]';
        }

        // Agregar event listeners a los campos
        document.getElementById('title').addEventListener('input', updatePreview);
        document.getElementById('content').addEventListener('input', updatePreview);
        document.getElementById('button_text').addEventListener('input', updatePreview);
        document.getElementById('display_type').addEventListener('change', updatePreview);
        document.getElementById('target_page').addEventListener('input', updatePreview);

        // Validación de fechas
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(this.value);
            
            if (endDate <= startDate) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        });

        // Eliminar anuncio
        function deleteAnnouncement() {
            if (confirm('¿Estás seguro de eliminar este anuncio? Esta acción no se puede deshacer.')) {
                fetch('{{ route("developer-web.announcements.destroy", $announcement->id) }}', {
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
                        window.location.href = '{{ route("developer-web.announcements.index") }}';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el anuncio');
                });
            }
        }
    </script>
</body>
</html>