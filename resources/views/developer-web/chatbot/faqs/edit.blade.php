<!DOCTYPE html>
<html>
<head>
    <title>Editar FAQ</title>
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
        textarea,
        select {
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
        .keywords-container {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: #f8f9fa;
        }
        .keyword-input {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .keyword-input input {
            flex: 1;
        }
        .keywords-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        .keyword-tag {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .keyword-tag button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 14px;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .faq-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Editar FAQ</h1>

        <div style="margin-bottom: 20px;">
            <a href="{{ route('developer-web.chatbot.faqs.index') }}" class="btn btn-secondary">
                ← Volver al Listado
            </a>
            <a href="{{ route('developer-web.chatbot.faqs.show', $faq->id) }}" class="btn btn-secondary" style="margin-left: 10px;">
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

        @if($errors->any())
            <div style="color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin-bottom: 15px;">
                <strong>Errores encontrados:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Información de la FAQ -->
        <div class="faq-info">
            <strong>ID:</strong> {{ $faq->id_faq ?? $faq->id }} | 
            <strong>Usos:</strong> {{ $faq->usage_count }} | 
            <strong>Creado:</strong> {{ $faq->created_date->format('d/m/Y H:i') }} | 
            <strong>Actualizado:</strong> {{ $faq->updated_date->format('d/m/Y H:i') }}
        </div>

        <form method="POST" action="{{ route('developer-web.chatbot.faqs.update', $faq->id) }}" id="faqForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="question">Pregunta *</label>
                <textarea id="question" name="question" required placeholder="Escribe la pregunta que harán los usuarios...">{{ old('question', $faq->question) }}</textarea>
                @error('question') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="answer">Respuesta *</label>
                <textarea id="answer" name="answer" required placeholder="Escribe la respuesta que dará el chatbot...">{{ old('answer', $faq->answer) }}</textarea>
                @error('answer') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Categoría *</label>
                    <select id="category" name="category" required>
                        <option value="">Seleccionar categoría</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" {{ (old('category', $faq->category) == $category) ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                        @endforeach
                    </select>
                    @error('category') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="new_category">O crear nueva categoría</label>
                    <input type="text" id="new_category" name="new_category" placeholder="Nueva categoría..." value="{{ old('new_category') }}">
                    @error('new_category') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Palabras Clave (Opcional)</label>
                <div class="keywords-container">
                    <div class="keyword-input">
                        <input type="text" id="keywordInput" placeholder="Agregar palabra clave..." maxlength="50">
                        <button type="button" onclick="addKeyword()" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px;">
                            Agregar
                        </button>
                    </div>
                    <div class="keywords-list" id="keywordsList">
                        <!-- Keywords se agregarán aquí dinámicamente -->
                    </div>
                    <input type="hidden" name="keywords" id="keywordsInput" value="{{ old('keywords', json_encode($faq->keywords ?? [])) }}">
                </div>
                @error('keywords') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="active">
                    <input type="checkbox" id="active" name="active" value="1" {{ old('active', $faq->active) ? 'checked' : '' }}>
                    FAQ Activa
                </label>
                <small style="display: block; color: #666;">Las FAQs inactivas no serán utilizadas por el chatbot.</small>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn btn-primary">Actualizar FAQ</button>
                <a href="{{ route('developer-web.chatbot.faqs.index') }}" class="btn btn-secondary" style="margin-left: 10px;">
                    Cancelar
                </a>
                <button type="button" onclick="deleteFaq()" class="btn btn-danger" style="margin-left: 10px;">
                    Eliminar FAQ
                </button>
            </div>
        </form>
    </div>

    <script>
        // Inicializar keywords desde old() o desde la FAQ
        let keywords = {!! json_encode(old('keywords', $faq->keywords ?? [])) !!};
        
        // Si keywords es string (por error), convertirlo a array
        if (typeof keywords === 'string') {
            try {
                keywords = JSON.parse(keywords);
            } catch (e) {
                keywords = [];
            }
        }
        
        // Asegurarse de que keywords sea un array
        if (!Array.isArray(keywords)) {
            keywords = [];
        }

        // Actualizar la lista de keywords visual
        function updateKeywordsList() {
            const keywordsList = document.getElementById('keywordsList');
            keywordsList.innerHTML = '';
            
            keywords.forEach((keyword, index) => {
                const keywordTag = document.createElement('div');
                keywordTag.className = 'keyword-tag';
                keywordTag.innerHTML = `
                    ${keyword}
                    <button type="button" onclick="removeKeyword(${index})">×</button>
                `;
                keywordsList.appendChild(keywordTag);
            });
            
            // Actualizar input hidden - asegurarse de que sea JSON válido
            document.getElementById('keywordsInput').value = JSON.stringify(keywords);
        }

        // Agregar keyword
        function addKeyword() {
            const keywordInput = document.getElementById('keywordInput');
            const keyword = keywordInput.value.trim();
            
            if (keyword && !keywords.includes(keyword)) {
                keywords.push(keyword);
                updateKeywordsList();
                keywordInput.value = '';
            }
        }

        // Remover keyword
        function removeKeyword(index) {
            keywords.splice(index, 1);
            updateKeywordsList();
        }

        // Permitir agregar con Enter
        document.getElementById('keywordInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addKeyword();
            }
        });

        // Manejar categoría existente vs nueva
        document.getElementById('category').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('new_category').value = '';
                document.getElementById('new_category').required = false;
                this.required = true;
            }
        });

        document.getElementById('new_category').addEventListener('input', function() {
            if (this.value) {
                document.getElementById('category').value = '';
                document.getElementById('category').required = false;
                this.required = true;
            }
        });

        // Validación del formulario
        document.getElementById('faqForm').addEventListener('submit', function(e) {
            const category = document.getElementById('category').value;
            const newCategory = document.getElementById('new_category').value;
            
            if (!category && !newCategory) {
                e.preventDefault();
                alert('Debes seleccionar una categoría existente o crear una nueva.');
                return false;
            }
            
            // Asegurarse de que keywords sea un array válido
            if (typeof keywords !== 'object' || !Array.isArray(keywords)) {
                keywords = [];
            }
            document.getElementById('keywordsInput').value = JSON.stringify(keywords);
        });

        // Eliminar FAQ
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

        // Inicializar lista de keywords
        updateKeywordsList();

        // Establecer requerido inicialmente
        document.getElementById('category').required = true;

        // Si hay una nueva categoría en old(), deseleccionar la categoría existente
        @if(old('new_category'))
            document.getElementById('category').value = '';
            document.getElementById('category').required = false;
            document.getElementById('new_category').required = true;
        @endif
    </script>
</body>
</html>