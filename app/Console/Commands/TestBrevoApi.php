<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestBrevoApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brevo:test-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar Brevo API directamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = env('BREVO_API_KEY');
        $senderEmail = env('BREVO_SENDER_EMAIL');
        $senderName = env('BREVO_SENDER_NAME');

        $this->info('🔧 Probando Brevo API...');
        $this->line('API Key: ' . substr($apiKey, 0, 20) . '...');
        $this->line('Sender: ' . $senderEmail);

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => $senderName,
                    'email' => $senderEmail,
                ],
                'to' => [
                    [
                        'email' => 'unsestudiante70@gmail.com',
                        'name' => 'Usuario de Prueba',
                    ]
                ],
                'subject' => '✅ Prueba Brevo API Exitosa',
                'htmlContent' => '
                    <html>
                        <body>
                            <h1>¡Funciona!</h1>
                            <p>El envío de emails via Brevo API está funcionando correctamente.</p>
                            <p><strong>Fecha:</strong> ' . now()->format('d/m/Y H:i') . '</p>
                        </body>
                    </html>
                ',
            ]);

            if ($response->successful()) {
                $this->info('🎉 ¡Email enviado correctamente via Brevo API!');
                $this->line('Message ID: ' . $response->json()['messageId']);
                $this->info('📧 Revisa la bandeja de entrada de: unsestudiante70@gmail.com');
            } else {
                $this->error('❌ Error: ' . $response->status());
                $this->line('Respuesta: ' . $response->body());
                
                // Mostrar más detalles del error
                if ($response->status() === 401) {
                    $this->line('🔐 Error de autenticación: Verifica tu API Key');
                } elseif ($response->status() === 400) {
                    $this->line('📧 Error en datos: Verifica el email remitente');
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Excepción: ' . $e->getMessage());
            $this->line('Archivo: ' . $e->getFile());
            $this->line('Línea: ' . $e->getLine());
        }
    }
}