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
        // NOTA: IncadevSeeder ya incluye TechnologySeeder que contiene:
        // - Permisos de Soporte Técnico y Seguridad
        // - Asignación de permisos a roles
        // - Datos de muestra de tickets
        $this->command->info('📦 Ejecutando seeder del vendor (IncadevSeeder)...');
        $this->call(\IncadevUns\CoreDomain\Database\Seeders\IncadevSeeder::class);

        // 2. Datos completos del sistema (pagos, encuestas, tickets, citas)
        $this->command->info('📊 Generando datos completos del sistema...');
        $this->call(CompleteSeeder::class);

        $this->command->info('✅ Proceso de seed completado exitosamente!');
    }
}
