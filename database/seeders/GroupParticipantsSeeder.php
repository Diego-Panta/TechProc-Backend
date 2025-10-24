<?php
// database/seeders/GroupParticipantsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GroupParticipantsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = DB::table('groups')->get();
        $users = DB::table('users')->get();
        $instructors = DB::table('instructors')->get();

        if ($groups->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No hay grupos o usuarios para asignar participantes.');
            return;
        }

        $participants = [];

        // Agregar instructores como profesores
        foreach ($groups as $group) {
            if ($instructors->isNotEmpty()) {
                $instructor = $instructors->random();
                $instructorUser = $users->where('id', $instructor->user_id)->first();
                
                if ($instructorUser) {
                    $participants[] = [
                        'group_id' => $group->id,
                        'user_id' => $instructorUser->id,
                        'role' => 'teacher',
                        'teacher_function' => ['titular', 'auxiliary', 'coordinator'][rand(0, 2)],
                        'enrollment_status' => 'active',
                        'assignment_date' => Carbon::now()->subDays(rand(1, 30)),
                        'schedule' => json_encode([
                            'days' => ['Monday', 'Wednesday'], 
                            'time' => '18:00-20:00',
                            'timezone' => 'America/Lima'
                        ]),
                    ];
                }
            }
        }

        // Agregar estudiantes a los grupos
        $studentUsers = $users->filter(function ($user) {
            $roles = json_decode($user->role, true);
            return is_array($roles) && in_array('student', $roles);
        });

        if ($studentUsers->isNotEmpty()) {
            foreach ($groups as $group) {
                // Agregar 3-5 estudiantes por grupo
                $randomStudents = $studentUsers->random(min(5, $studentUsers->count()));
                
                foreach ($randomStudents as $student) {
                    $participants[] = [
                        'group_id' => $group->id,
                        'user_id' => $student->id,
                        'role' => 'student',
                        'teacher_function' => null,
                        'enrollment_status' => ['pending', 'approved', 'active', 'active', 'active'][rand(0, 4)], // Más probabilidad de active
                        'assignment_date' => Carbon::now()->subDays(rand(1, 60)),
                        'schedule' => json_encode([
                            'preferred_days' => ['Monday', 'Wednesday', 'Friday'][rand(0, 2)],
                            'preferred_time' => ['morning', 'afternoon', 'evening'][rand(0, 2)]
                        ]),
                    ];
                }
            }
        }

        // Eliminar duplicados (por si acaso)
        $uniqueParticipants = collect($participants)->unique(function ($item) {
            return $item['group_id'] . '-' . $item['user_id'];
        })->values()->all();

        if (!empty($uniqueParticipants)) {
            DB::table('group_participants')->insert($uniqueParticipants);
            $this->command->info('Participantes de grupo creados: ' . count($uniqueParticipants));
            $this->command->info(' - Teachers: ' . collect($uniqueParticipants)->where('role', 'teacher')->count());
            $this->command->info(' - Students: ' . collect($uniqueParticipants)->where('role', 'student')->count());
        } else {
            $this->command->warn('No se crearon participantes de grupo.');
        }
    }
}