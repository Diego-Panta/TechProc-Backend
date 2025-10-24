{{-- resources/views/emails/contact-form-response.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Respuesta a tu consulta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #667eea;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            border: 1px solid #ddd;
        }
        .response {
            background: white;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 5px 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Hola {{ $fullName }}!</h1>
        <p>Hemos respondido a tu consulta</p>
    </div>

    <div class="content">
        <p>Gracias por contactarnos. Aquí está la respuesta a tu consulta "<strong>{{ $originalSubject }}</strong>":</p>

        <div class="response">
            <p>{!! nl2br(e($response)) !!}</p>
        </div>

        <p>Si tienes más preguntas, no dudes en contactarnos nuevamente.</p>

        <div class="footer">
            <p><strong>Fecha de respuesta:</strong> {{ $responseDate }}</p>
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>