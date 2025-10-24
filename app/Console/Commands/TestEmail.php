<?php
// app/Console/Commands/TestEmail.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test:email {email?}';
    protected $description = 'Probar configuración de email';

    public function handle()
    {
        $testEmail = $this->argument('email') ?: 'diegob6.14.2003@gmail.com';
        
        $this->info("📧 Probando envío a: {$testEmail}");
        $this->info("📤 Desde: " . config('mail.from.address'));
        
        try {
            Mail::raw('Este es un email de prueba desde Laravel. Si recibes esto, el email está funcionando correctamente.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('✅ Prueba de Email Exitosa - ' . config('app.name'));
            });

            $this->info('✅ ¡Email enviado correctamente!');
            $this->info('📨 Revisa la bandeja de entrada y spam del correo destino.');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            
            // Información adicional para debugging
            $this->line('');
            $this->line('🔧 Para solucionar:');
            $this->line('1. Verifica que MAIL_FROM_ADDRESS sea igual a MAIL_USERNAME');
            $this->line('2. Usa un correo diferente temporalmente: php artisan test:email otro-correo@gmail.com');
            $this->line('3. Espera 1-2 horas si el límite de Gmail persiste');
        }
    }
}