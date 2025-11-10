<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Enviar un email de prueba';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info('Enviando email de prueba a: ' . $email);

        try {
            Mail::raw('Este es un email de prueba desde TechProc Backend', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Email de Prueba - TechProc');
            });

            $this->info('✓ Email enviado exitosamente');
            $this->info('Revisa tu bandeja de entrada y carpeta de spam');

        } catch (\Exception $e) {
            $this->error('✗ Error al enviar email:');
            $this->error($e->getMessage());
        }
    }
}
