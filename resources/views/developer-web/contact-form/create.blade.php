<!DOCTYPE html>
<html>
<head>
    <title>Contacto</title>
</head>
<body>
    <h1>Formulario de Contacto</h1>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('public.contact.store') }}">
        @csrf
        
        <div>
            <label for="full_name">Nombre Completo:</label>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required>
            @error('full_name') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="phone">Teléfono:</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
            @error('phone') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="company">Empresa:</label>
            <input type="text" id="company" name="company" value="{{ old('company') }}">
            @error('company') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="subject">Asunto:</label>
            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>
            @error('subject') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="message">Mensaje:</label>
            <textarea id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
            @error('message') <div style="color: red;">{{ $message }}</div> @enderror
        </div>

        <button type="submit">Enviar Mensaje</button>
    </form>
</body>
</html>