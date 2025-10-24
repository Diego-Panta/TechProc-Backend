<?php
// database/seeders/AttendancesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesSeeder extends Seeder
{
    public function run(): void
    {
        $groupParticipants = DB::table('group_participants')->where('role', 'student')->get();
        $classes = DB::table('classes')->get();

        if ($groupParticipants->isEmpty() || $classes->isEmpty()) {
            $this->command->warn('No hay participantes de grupo o clases para crear asistencias.');
            return;
        }

        $attendances = [];

        foreach ($groupParticipants as $participant) {
            // Tomar algunas clases aleatorias para este participante (no todas)
            $randomClasses = $classes->random(min(5, $classes->count()));
            
            foreach ($randomClasses as $class) {
                $attended = rand(0, 1) ? 'YES' : 'NO';
                $entryTime = $attended === 'YES' ? Carbon::now()->subHours(2) : null;
                
                $attendances[] = [
                    'group_participant_id' => $participant->id,
                    'class_id' => $class->id,
                    'attended' => $attended,
                    'entry_time' => $entryTime,
                    'exit_time' => $attended === 'YES' ? Carbon::now()->subHours(1) : null,
                    'connected_minutes' => $attended === 'YES' ? rand(45, 120) : 0,
                    'connection_ip' => $attended === 'YES' ? '192.168.1.' . rand(1, 255) : null,
                    'device' => $attended === 'YES' ? ['Windows Chrome', 'Mac Safari', 'Android App', 'iOS App'][rand(0, 3)] : null,
                    'approximate_location' => $attended === 'YES' ? 'Lima, Perú' : null,
                    'connection_quality' => $attended === 'YES' ? ['EXCELLENT', 'GOOD', 'FAIR', 'POOR'][rand(0, 3)] : null,
                    'observations' => $attended === 'YES' ? 'Asistencia registrada correctamente' : 'Inasistencia justificada',
                    'cloud_synchronized' => true,
                    'record_date' => Carbon::now()->subDays(rand(1, 30)),
                ];
            }
        }

        // Eliminar duplicados por si acaso
        $uniqueAttendances = collect($attendances)->unique(function ($item) {
            return $item['group_participant_id'] . '-' . $item['class_id'];
        })->values()->all();

        if (!empty($uniqueAttendances)) {
            DB::table('attendances')->insert($uniqueAttendances);
            $this->command->info('Asistencias creadas: ' . count($uniqueAttendances));
            $this->command->info(' - Asistencias (YES): ' . collect($uniqueAttendances)->where('attended', 'YES')->count());
            $this->command->info(' - Inasistencias (NO): ' . collect($uniqueAttendances)->where('attended', 'NO')->count());
        }
    }
}