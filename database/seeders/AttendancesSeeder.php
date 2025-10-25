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
                $attended = (bool)rand(0, 1); // true o false para boolean
                
                $attendances[] = [
                    'group_participant_id' => $participant->id,
                    'class_id' => $class->id,
                    'attended' => $attended,
                    'observations' => $attended ? 'Asistencia registrada correctamente' : 'Inasistencia justificada',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // Eliminar duplicados por si acaso (debido a la constraint UNIQUE)
        $uniqueAttendances = collect($attendances)->unique(function ($item) {
            return $item['group_participant_id'] . '-' . $item['class_id'];
        })->values()->all();

        if (!empty($uniqueAttendances)) {
            DB::table('attendances')->insert($uniqueAttendances);
            $this->command->info('Asistencias creadas: ' . count($uniqueAttendances));
            $this->command->info(' - Asistencias (true): ' . collect($uniqueAttendances)->where('attended', true)->count());
            $this->command->info(' - Inasistencias (false): ' . collect($uniqueAttendances)->where('attended', false)->count());
        }
    }
}