<?php
// database/seeders/InstructorsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstructorsSeeder extends Seeder
{
    public function run(): void
    {
        // Primero verificar si existen usuarios con rol instructor
        $instructorUsers = DB::table('users')->where('role', 'like', '%instructor%')->get();

        if ($instructorUsers->isEmpty()) {
            $this->command->warn('No hay usuarios con rol instructor. Creando usuarios instructores...');
            
            // Crear usuarios instructores
            $instructorData = [
                [
                    'first_name' => 'Carlos',
                    'last_name' => 'Rodríguez',
                    'full_name' => 'Carlos Rodríguez',
                    'dni' => '11223344',
                    'document' => '11223344',
                    'email' => 'carlos.rodriguez@email.com',
                    'phone_number' => '+51987654323',
                    'password' => bcrypt('password123'),
                    'gender' => 'male',
                    'country' => 'Perú',
                    'role' => json_encode(['instructor']),
                    'status' => 'active',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'first_name' => 'Laura',
                    'last_name' => 'Martínez',
                    'full_name' => 'Laura Martínez',
                    'dni' => '22334455',
                    'document' => '22334455',
                    'email' => 'laura.martinez@email.com',
                    'phone_number' => '+51987654325',
                    'password' => bcrypt('password123'),
                    'gender' => 'female',
                    'country' => 'Perú',
                    'role' => json_encode(['instructor']),
                    'status' => 'active',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ];

            foreach ($instructorData as $userData) {
                $userId = DB::table('users')->insertGetId($userData);
                $instructorUsers->push((object)['id' => $userId]);
            }
        }

        $instructors = [];
        $instructorId = 9000;

        foreach ($instructorUsers as $index => $user) {
            $instructors[] = [
                'instructor_id' => $instructorId + $index,
                'user_id' => $user->id,
                'bio' => 'Instructor con amplia experiencia en ' . ['desarrollo web', 'ciencia de datos', 'programación'][$index % 3],
                'expertise_area' => ['Full Stack Development', 'Data Science', 'Programming Fundamentals'][$index % 3],
                'status' => 'active',
                'created_at' => Carbon::now(),
            ];
        }

        DB::table('instructors')->insert($instructors);
        $this->command->info('Instructores creados: ' . count($instructors));
    }
}