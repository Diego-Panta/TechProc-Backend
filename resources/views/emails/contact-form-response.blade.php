{{-- resources/views/emails/contact-form-response-simple.blade.php --}}
<!DOCTYPE html>
<html>
<body>
    <h2>Hola {{ $fullName }},</h2>
    
    <p>Gracias por contactarnos. Aquí está nuestra respuesta:</p>
    
    <div style="background: #f5f5f5; padding: 15px; margin: 10px 0;">
        {!! nl2br(e($response)) !!}
    </div>
    
    <p>Saludos,<br>Equipo de Soporte</p>
    
    <hr>
    <small>Este es un mensaje automático. Por favor no respondas a este correo.</small>
</body>
</html>