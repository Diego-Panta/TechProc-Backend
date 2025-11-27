<?php
/**
 * Script para verificar configuración de email del formulario de contacto
 *
 * Uso:
 * php test-contact-form-email.php
 * O desde navegador: https://tudominio.com/backend/tecnologico/public/test-contact-form-email.php
 */

// Cargar Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "==============================================\n";
echo "  TEST CONFIGURACIÓN EMAIL CONTACTO\n";
echo "==============================================\n\n";

try {
    // 1. Verificar configuración de .env
    echo "📋 CONFIGURACIÓN ACTUAL:\n";
    echo "─────────────────────────────────────────────\n\n";

    echo "Entorno: " . env('APP_ENV') . "\n";
    echo "URL: " . env('APP_URL') . "\n\n";

    echo "🔧 GMAIL SMTP:\n";
    echo "   MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
    echo "   MAIL_HOST: " . env('MAIL_HOST') . "\n";
    echo "   MAIL_PORT: " . env('MAIL_PORT') . "\n";
    echo "   MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
    echo "   MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? str_repeat('*', strlen(env('MAIL_PASSWORD'))) : 'NO CONFIGURADO') . "\n";
    echo "   MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n";
    echo "   MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
    echo "   MAIL_FROM_NAME: " . env('MAIL_FROM_NAME') . "\n\n";

    echo "📧 BREVO API:\n";
    echo "   BREVO_API_KEY: " . (env('BREVO_API_KEY') ? substr(env('BREVO_API_KEY'), 0, 20) . '...' : 'NO CONFIGURADO') . "\n";
    echo "   BREVO_SENDER_EMAIL: " . env('BREVO_SENDER_EMAIL') . "\n";
    echo "   BREVO_SENDER_NAME: " . env('BREVO_SENDER_NAME') . "\n\n";

    echo "⚙️ CONFIGURACIÓN:\n";
    echo "   USE_GMAIL_AS_PRIMARY: " . (env('USE_GMAIL_AS_PRIMARY') ? 'true' : 'false') . "\n";
    echo "   ADMIN_EMAIL: " . env('ADMIN_EMAIL') . "\n\n";

    // 2. Validar configuración
    echo "==============================================\n";
    echo "  VALIDACIÓN DE CONFIGURACIÓN\n";
    echo "==============================================\n\n";

    $errors = [];
    $warnings = [];

    // Validar MAIL_USERNAME
    $mailUsername = env('MAIL_USERNAME');
    if (empty($mailUsername)) {
        $errors[] = "MAIL_USERNAME está vacío";
    } elseif (!filter_var($mailUsername, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "MAIL_USERNAME no es un email válido: '$mailUsername' (debería ser un email como unsestudiante70@gmail.com)";
    } else {
        echo "   ✅ MAIL_USERNAME es válido: $mailUsername\n";
    }

    // Validar MAIL_PASSWORD
    if (empty(env('MAIL_PASSWORD'))) {
        $errors[] = "MAIL_PASSWORD está vacío";
    } else {
        echo "   ✅ MAIL_PASSWORD está configurado\n";
    }

    // Validar MAIL_FROM_ADDRESS
    if (empty(env('MAIL_FROM_ADDRESS'))) {
        $errors[] = "MAIL_FROM_ADDRESS está vacío";
    } elseif (!filter_var(env('MAIL_FROM_ADDRESS'), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "MAIL_FROM_ADDRESS no es un email válido";
    } else {
        echo "   ✅ MAIL_FROM_ADDRESS es válido: " . env('MAIL_FROM_ADDRESS') . "\n";
    }

    // Validar ADMIN_EMAIL
    if (empty(env('ADMIN_EMAIL'))) {
        $warnings[] = "ADMIN_EMAIL no está configurado (no se recibirán notificaciones)";
    } elseif (!filter_var(env('ADMIN_EMAIL'), FILTER_VALIDATE_EMAIL)) {
        $errors[] = "ADMIN_EMAIL no es un email válido";
    } else {
        echo "   ✅ ADMIN_EMAIL es válido: " . env('ADMIN_EMAIL') . "\n";
    }

    // Validar Brevo API
    if (env('USE_GMAIL_AS_PRIMARY') === false) {
        if (empty(env('BREVO_API_KEY'))) {
            $errors[] = "BREVO_API_KEY está vacío (y está configurado como método primario)";
        } else {
            echo "   ✅ BREVO_API_KEY está configurado\n";
        }
    }

    echo "\n";

    // Mostrar errores
    if (count($errors) > 0) {
        echo "❌ ERRORES ENCONTRADOS:\n";
        foreach ($errors as $error) {
            echo "   • $error\n";
        }
        echo "\n";
    }

    // Mostrar advertencias
    if (count($warnings) > 0) {
        echo "⚠️  ADVERTENCIAS:\n";
        foreach ($warnings as $warning) {
            echo "   • $warning\n";
        }
        echo "\n";
    }

    if (count($errors) > 0) {
        echo "==============================================\n";
        echo "  ❌ CONFIGURACIÓN INVÁLIDA\n";
        echo "==============================================\n\n";
        echo "Por favor, corrige los errores en el archivo .env y ejecuta:\n";
        echo "php artisan config:clear\n\n";
        exit(1);
    }

    // 3. Test de envío de email
    echo "==============================================\n";
    echo "  TEST DE ENVÍO DE EMAIL\n";
    echo "==============================================\n\n";

    $testEmail = env('ADMIN_EMAIL');
    $subject = "Test de formulario de contacto - " . now()->format('Y-m-d H:i:s');
    $content = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
    </head>
    <body style='font-family: Arial, sans-serif; padding: 20px;'>
        <h2>🧪 Test de Email - Formulario de Contacto</h2>
        <p>Este es un email de prueba para verificar la configuración.</p>

        <div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0;'>
            <strong>Configuración detectada:</strong><br>
            Entorno: " . env('APP_ENV') . "<br>
            Método primario: " . (env('USE_GMAIL_AS_PRIMARY') ? 'Gmail SMTP' : 'Brevo API') . "<br>
            Fecha: " . now()->format('Y-m-d H:i:s') . "
        </div>

        <p>Si recibes este email, la configuración es correcta ✅</p>
    </body>
    </html>
    ";

    echo "📤 Intentando enviar email de prueba a: $testEmail\n\n";

    try {
        if (env('USE_GMAIL_AS_PRIMARY')) {
            echo "   Método: Gmail SMTP\n";

            Mail::html($content, function ($message) use ($testEmail, $subject) {
                $message->to($testEmail)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });

            echo "   ✅ Email enviado exitosamente via Gmail\n\n";
        } else {
            echo "   Método: Brevo API\n";

            $brevoApiKey = env('BREVO_API_KEY');
            $emailData = [
                'sender' => [
                    'name' => env('BREVO_SENDER_NAME'),
                    'email' => env('BREVO_SENDER_EMAIL'),
                ],
                'to' => [
                    ['email' => $testEmail]
                ],
                'subject' => $subject,
                'htmlContent' => $content,
            ];

            $response = Http::withHeaders([
                'api-key' => $brevoApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', $emailData);

            if ($response->successful()) {
                echo "   ✅ Email enviado exitosamente via Brevo\n";
                echo "   Message ID: " . $response->json('messageId') . "\n\n";
            } else {
                echo "   ❌ Error enviando via Brevo\n";
                echo "   Status: " . $response->status() . "\n";
                echo "   Error: " . $response->body() . "\n\n";
            }
        }
    } catch (\Exception $e) {
        echo "   ❌ Error al enviar email: " . $e->getMessage() . "\n\n";
        throw $e;
    }

    // 4. Verificar endpoint de contacto
    echo "==============================================\n";
    echo "  ENDPOINT DE CONTACTO\n";
    echo "==============================================\n\n";

    echo "📍 Endpoint público: POST " . env('APP_URL') . "/api/contact-forms\n\n";

    echo "📝 Ejemplo de uso:\n\n";
    echo "curl -X POST " . env('APP_URL') . "/api/contact-forms \\\n";
    echo "  -H \"Content-Type: application/json\" \\\n";
    echo "  -d '{\n";
    echo "    \"full_name\": \"Juan Pérez\",\n";
    echo "    \"email\": \"juan@example.com\",\n";
    echo "    \"subject\": \"Consulta sobre cursos\",\n";
    echo "    \"message\": \"Quiero información sobre los cursos disponibles\",\n";
    echo "    \"phone\": \"999999999\",\n";
    echo "    \"company\": \"Mi Empresa\"\n";
    echo "  }'\n\n";

    // Resumen final
    echo "==============================================\n";
    echo "  ✅ RESUMEN\n";
    echo "==============================================\n\n";

    if (count($errors) === 0) {
        echo "✅ La configuración de email es correcta\n";
        echo "✅ Se envió un email de prueba a: $testEmail\n";
        echo "✅ El endpoint de contacto está disponible\n\n";

        echo "🔍 REVISA TU BANDEJA DE ENTRADA:\n";
        echo "   Email: $testEmail\n";
        echo "   Asunto: $subject\n\n";

        echo "Si NO recibes el email:\n";
        echo "1. Revisa la carpeta de spam\n";
        echo "2. Verifica que las credenciales Gmail sean correctas\n";
        echo "3. Verifica que la contraseña de aplicación de Gmail sea válida\n";
        echo "4. Revisa los logs: storage/logs/laravel.log\n\n";
    } else {
        echo "❌ Hay errores en la configuración\n";
        echo "   Por favor, corrígelos antes de continuar\n\n";
    }

    echo "==============================================\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
