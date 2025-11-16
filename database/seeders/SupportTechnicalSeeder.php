<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SupportTechnicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Para ejecutar este seeder:
     * php artisan db:seed --class=SupportTechnicalSeeder
     * 
     * O para ejecutar solo permisos:
     * php artisan db:seed --class=AssignSupportTechnicalPermissionsSeeder
     * 
     * O para ejecutar solo datos de muestra:
     * php artisan db:seed --class=SupportTechnicalSampleDataSeeder
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Seeder del Módulo SupportTechnical');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');

        // 1. Assign permissions to roles
        $this->command->info('1. Asignando permisos a roles...');
        $this->call(AssignSupportTechnicalPermissionsSeeder::class);
        $this->command->info('');

        // 2. Create sample data
        $this->command->info('2. Generando datos de muestra...');
        $this->call(SupportTechnicalSampleDataSeeder::class);
        $this->command->info('');

        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  ✓ Seeder completado exitosamente');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('Puedes verificar los datos creados:');
        $this->command->info('  - Tickets: php artisan tinker -> \\IncadevUns\\CoreDomain\\Models\\Ticket::count()');
        $this->command->info('  - Replies: php artisan tinker -> \\IncadevUns\\CoreDomain\\Models\\TicketReply::count()');
        $this->command->info('');
    }
}
