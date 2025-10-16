<!DOCTYPE html>
<html>
<head>
    <title>Crear Nueva Noticia</title>
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
        .error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        .tag-input {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 40px;
        }
        .tag {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .tag-remove {
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Crear Nueva Noticia</h1>

        <div style="margin-bottom: 20px;">
            <a href="{{ route('developer-web.news.index') }}" class="btn btn-secondary">
                ← Volver al Listado
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

        <form method="POST" action="{{ route('developer-web.news.store') }}" id="newsForm">
            @csrf

            <div class="form-group">
                <label for="title">Título de la Noticia *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required 
                       oninput="updateSlugPreview()">
                @error('title') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="slug">Slug (URL amigable)</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" 
                       placeholder="Se generará automáticamente si se deja vacío">
                @error('slug') <div class="error">{{ $message }}</div> @enderror
                <div id="slug-preview" style="font-size: 12px; color: #666; margin-top: 5px;">
                    URL: <span id="slug-preview-text">[preview]</span>
                </div>
            </div>

            <div class="form-group">
                <label for="summary">Resumen *</label>
                <textarea id="summary" name="summary" required maxlength="500">{{ old('summary') }}</textarea>
                <div style="font-size: 12px; color: #666; text-align: right;">
                    <span id="summary-counter">0</span>/500 caracteres
                </div>
                @error('summary') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="content">Contenido Completo *</label>
                <textarea id="content" name="content" required style="min-height: 300px;">{{ old('content') }}</textarea>
                @error('content') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Categoría *</label>
                    <input type="text" id="category" name="category" value="{{ old('category') }}" 
                           placeholder="Ej: Educación, Tecnología, Eventos" required>
                    @error('category') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="status">Estado *</label>
                    <select id="status" name="status" required>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado</option>
                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archivado</option>
                    </select>
                    @error('status') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="tags">Etiquetas</label>
                <div class="tag-input" id="tag-container">
                    <input type="text" id="tag-input" placeholder="Escribe una etiqueta y presiona Enter" 
                           style="border: none; outline: none; flex: 1; min-width: 200px;">
                </div>
                <input type="hidden" id="tags" name="tags" value="{{ old('tags', '[]') }}">
                @error('tags') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="featured_image">URL de la Imagen Destacada</label>
                    <input type="url" id="featured_image" name="featured_image" value="{{ old('featured_image') }}" 
                           placeholder="https://ejemplo.com/imagen.jpg">
                    @error('featured_image') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="published_date">Fecha de Publicación</label>
                    <input type="datetime-local" id="published_date" name="published_date" 
                           value="{{ old('published_date') }}">
                    @error('published_date') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="seo_title">Título SEO</label>
                    <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title') }}" 
                           placeholder="Título para motores de búsqueda">
                    @error('seo_title') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="seo_description">Descripción SEO</label>
                    <textarea id="seo_description" name="seo_description" 
                              placeholder="Descripción para motores de búsqueda" 
                              style="min-height: 80px;">{{ old('seo_description') }}</textarea>
                    @error('seo_description') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Vista Previa -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <h3>Vista Previa</h3>
                <div id="preview-content" style="padding: 15px; border: 1px dashed #ccc; border-radius: 5px;">
                    <h4 id="preview-title">[Título de la noticia]</h4>
                    <p><strong>Slug:</strong> <span id="preview-slug">[slug]</span></p>
                    <p><strong>Resumen:</strong> <span id="preview-summary">[Resumen de la noticia]</span></p>
                    <p><strong>Categoría:</strong> <span id="preview-category">[Categoría]</span></p>
                    <div id="preview-tags">
                        <strong>Etiquetas:</strong> <span>[ninguna]</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn btn-primary">Crear Noticia</button>
                <a href="{{ route('developer-web.news.index') }}" class="btn btn-secondary" style="margin-left: 10px;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
        let tags = [];

        // Inicializar tags desde el valor antiguo si existe
        @if(old('tags'))
            try {
                tags = JSON.parse('{{ old('tags') }}');
                renderTags();
            } catch (e) {
                console.error('Error parsing tags:', e);
            }
        @endif

        // Actualizar vista previa
        function updatePreview() {
            document.getElementById('preview-title').textContent = 
                document.getElementById('title').value || '[Título de la noticia]';
            document.getElementById('preview-slug').textContent = 
                document.getElementById('slug').value || '[slug-generado]';
            document.getElementById('preview-summary').textContent = 
                document.getElementById('summary').value || '[Resumen de la noticia]';
            document.getElementById('preview-category').textContent = 
                document.getElementById('category').value || '[Categoría]';
            
            // Actualizar tags en vista previa
            const tagsPreview = document.getElementById('preview-tags');
            if (tags.length > 0) {
                tagsPreview.innerHTML = '<strong>Etiquetas:</strong> ' + tags.map(tag => 
                    `<span style="background: #e9ecef; padding: 2px 6px; border-radius: 10px; font-size: 12px;">${tag}</span>`
                ).join(' ');
            } else {
                tagsPreview.innerHTML = '<strong>Etiquetas:</strong> <span>[ninguna]</span>';
            }
        }

        // Actualizar slug preview
        function updateSlugPreview() {
            const title = document.getElementById('title').value;
            const slugInput = document.getElementById('slug');
            
            if (!slugInput.value) {
                // Generar slug automáticamente
                const slug = title.toLowerCase()
                    .replace(/[^\w\s]/g, '')
                    .replace(/\s+/g, '-')
                    .substring(0, 255);
                document.getElementById('slug-preview-text').textContent = slug;
            } else {
                document.getElementById('slug-preview-text').textContent = slugInput.value;
            }
            
            updatePreview();
        }

        // Contador de caracteres para resumen
        document.getElementById('summary').addEventListener('input', function() {
            document.getElementById('summary-counter').textContent = this.value.length;
            updatePreview();
        });

        // Sistema de tags
        document.getElementById('tag-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tag = this.value.trim();
                if (tag && !tags.includes(tag)) {
                    tags.push(tag);
                    renderTags();
                    updateHiddenTags();
                    updatePreview();
                }
                this.value = '';
            }
        });

        function renderTags() {
            const container = document.getElementById('tag-container');
            // Limpiar tags existentes excepto el input
            const existingTags = container.querySelectorAll('.tag');
            existingTags.forEach(tag => tag.remove());
            
            // Renderizar tags
            tags.forEach((tag, index) => {
                const tagElement = document.createElement('span');
                tagElement.className = 'tag';
                tagElement.innerHTML = `
                    ${tag}
                    <span class="tag-remove" onclick="removeTag(${index})">×</span>
                `;
                container.insertBefore(tagElement, document.getElementById('tag-input'));
            });
        }

        function removeTag(index) {
            tags.splice(index, 1);
            renderTags();
            updateHiddenTags();
            updatePreview();
        }

        function updateHiddenTags() {
            document.getElementById('tags').value = JSON.stringify(tags || []);
        }

        // Event listeners para actualizar vista previa
        document.getElementById('title').addEventListener('input', updateSlugPreview);
        document.getElementById('slug').addEventListener('input', updatePreview);
        document.getElementById('category').addEventListener('input', updatePreview);

        // Inicializar vista previa
        updatePreview();
        document.getElementById('summary-counter').textContent = 
            document.getElementById('summary').value.length;
    </script>
</body>
</html>