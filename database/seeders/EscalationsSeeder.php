<?php
// database/seeders/EscalationsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EscalationsSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = DB::table('tickets')->whereIn('status', ['en_progreso', 'resuelto'])->get();
        $employees = DB::table('employees')->get();

        // Verificar que hay al menos 2 empleados para escalaciones
        if ($employees->count() < 2) {
            $this->command->warn('No hay suficientes empleados para crear escalaciones. Se necesitan al menos 2.');
            return;
        }

        $escalations = [];
        $escalationId = 4000;

        $reasons = [
            'Requiere especialización técnica avanzada',
            'Cliente solicita cambio de técnico',
            'Tiempo de resolución excedido',
            'Problema complejo que requiere mayor experiencia'
        ];

        foreach ($tickets as $index => $ticket) {
            // Obtener 2 empleados diferentes
            $technicians = $employees->random(2);
            
            $escalations[] = [
                'escalation_id' => $escalationId + $index,
                'ticket_id' => $ticket->id,
                'technician_origin_id' => $technicians[0]->id,
                'technician_destiny_id' => $technicians[1]->id,
                'escalation_reason' => $reasons[rand(0, 3)],
                'observations' => 'Escalación realizada por complejidad del caso.',
                'escalation_date' => Carbon::now()->subDays(rand(1, 20)),
                'approved' => (bool)rand(0, 1),
            ];
        }

        DB::table('escalations')->insert($escalations);
        $this->command->info('Escalaciones creadas: ' . count($escalations));
    }
}