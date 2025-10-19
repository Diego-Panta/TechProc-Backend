<!DOCTYPE html>
<html>
<head>
    <title>Contacto</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        .btn {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Formulario de Contacto</h1>

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

        <form method="POST" action="{{ route('public.contact.store') }}">
            @csrf
            
            <div class="form-group">
                <label for="full_name" class="form-label">Nombre Completo:</label>
                <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                @error('full_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Teléfono:</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                @error('phone') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="company" class="form-label">Empresa:</label>
                <input type="text" id="company" name="company" class="form-control" value="{{ old('company') }}">
                @error('company') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="form_type" class="form-label">Tipo de Consulta:</label>
                <select id="form_type" name="form_type" class="form-control">
                    <option value="general" {{ old('form_type') == 'general' ? 'selected' : '' }}>General</option>
                    <option value="sales" {{ old('form_type') == 'sales' ? 'selected' : '' }}>Ventas</option>
                    <option value="support" {{ old('form_type') == 'support' ? 'selected' : '' }}>Soporte</option>
                    <option value="technical" {{ old('form_type') == 'technical' ? 'selected' : '' }}>Técnico</option>
                </select>
                @error('form_type') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="subject" class="form-label">Asunto:</label>
                <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject') }}" required>
                @error('subject') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="message" class="form-label">Mensaje:</label>
                <textarea id="message" name="message" class="form-control" required>{{ old('message') }}</textarea>
                @error('message') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn">Enviar Mensaje</button>
        </form>
    </div>
</body>
</html>