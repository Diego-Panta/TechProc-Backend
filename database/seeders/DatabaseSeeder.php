<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Ejecuta todos los seeders en el orden correcto:
     * 1. Vendor seeders (usuarios base, roles, permisos)
     * 2. Seeders locales (soporte técnico, datos de muestra)
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando proceso de seed...');

        // 1. Seeder principal del vendor (usuarios, roles, permisos base)
        $this->command->info('📦 Ejecutando seeder del vendor (IncadevSeeder)...');
        $this->call(\IncadevUns\CoreDomain\Database\Seeders\IncadevSeeder::class);

        // 2. Módulo de Soporte Técnico
        $this->command->info('🎫 Configurando módulo de Soporte Técnico...');
        $this->call(SupportTechnicalSeeder::class);

        // 3. Asignar permisos al módulo de Soporte Técnico
        $this->command->info('🔐 Asignando permisos de Soporte Técnico...');
        $this->call(AssignSupportTechnicalPermissionsSeeder::class);

        // 4. Datos completos del sistema (pagos, encuestas, tickets, citas)
        $this->command->info('📊 Generando datos completos del sistema...');
        $this->call(CompleteSeeder::class);

        // 5. Datos de muestra para Soporte Técnico
        $this->command->info('🎭 Generando datos de muestra para Soporte Técnico...');
        $this->call(SupportTechnicalSampleDataSeeder::class);

        $this->command->info('✅ Proceso de seed completado exitosamente!');
    }
}
