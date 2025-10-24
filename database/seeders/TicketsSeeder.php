<?php
// database/seeders/TicketsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketsSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();
        $employees = DB::table('employees')->get();

        $tickets = [];
        $ticketId = 2000;

        $priorities = ['baja', 'media', 'alta', 'critica'];
        $statuses = ['abierto', 'en_progreso', 'resuelto', 'cerrado'];
        $categories = ['tecnico', 'academico', 'financiero', 'general'];

        foreach ($users as $index => $user) {
            $tickets[] = [
                'ticket_id' => $ticketId + $index,
                'assigned_technician' => $employees->random()->id,
                'user_id' => $user->id,
                'title' => 'Problema con ' . ['acceso al aula virtual', 'subida de archivos', 'conexión VPN', 'certificado'][rand(0, 3)],
                'description' => 'Necesito ayuda para resolver un problema técnico que estoy experimentando en la plataforma.',
                'priority' => $priorities[rand(0, 3)],
                'status' => $statuses[rand(0, 3)],
                'creation_date' => Carbon::now()->subDays(rand(1, 60)),
                'assignment_date' => Carbon::now()->subDays(rand(1, 30)),
                'resolution_date' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 15)) : null,
                'close_date' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 10)) : null,
                'category' => $categories[rand(0, 3)],
                'notes' => 'Cliente contactado, se requiere seguimiento.',
            ];
        }

        DB::table('tickets')->insert($tickets);
    }
}