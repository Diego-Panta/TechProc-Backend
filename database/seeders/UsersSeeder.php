<?php
// database/seeders/UsersSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\AuthenticationSessions\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ========================================
            // GRUPO 03 - SOPORTE Y ADMINISTRACIÓN
            // ========================================
            [
                'name' => 'super_admin',
                'dni' => '10000001',
                'fullname' => 'Super Admin',
                'email' => 'super.admin@techproc.com',
                'phone' => '+51900000001',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'admin',
                'dni' => '10000002',
                'fullname' => 'Admin Principal',
                'email' => 'admin@techproc.com',
                'phone' => '+51900000002',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'name' => 'support',
                'dni' => '10000003',
                'fullname' => 'Carlos Soporte',
                'email' => 'support@techproc.com',
                'phone' => '+51900000003',
                'password' => Hash::make('password123'),
                'role' => 'support',
            ],
            [
                'name' => 'infrastructure',
                'dni' => '10000004',
                'fullname' => 'Luis Infraestructura',
                'email' => 'infrastructure@techproc.com',
                'phone' => '+51900000004',
                'password' => Hash::make('password123'),
                'role' => 'infrastructure',
            ],
            [
                'name' => 'security',
                'dni' => '10000005',
                'fullname' => 'Ana Seguridad',
                'email' => 'security@techproc.com',
                'phone' => '+51900000005',
                'password' => Hash::make('password123'),
                'role' => 'security',
            ],
            [
                'name' => 'academic_analyst',
                'dni' => '10000006',
                'fullname' => 'María Analista',
                'email' => 'academic.analyst@techproc.com',
                'phone' => '+51900000006',
                'password' => Hash::make('password123'),
                'role' => 'academic_analyst',
            ],
            [
                'name' => 'web',
                'dni' => '10000007',
                'fullname' => 'Pedro Web',
                'email' => 'web@techproc.com',
                'phone' => '+51900000007',
                'password' => Hash::make('password123'),
                'role' => 'web',
            ],

            // ========================================
            // GRUPO 06 - AUDITORÍA Y ENCUESTAS
            // ========================================
            [
                'name' => 'survey_admin',
                'dni' => '10000008',
                'fullname' => 'Laura Encuestas',
                'email' => 'survey.admin@techproc.com',
                'phone' => '+51900000008',
                'password' => Hash::make('password123'),
                'role' => 'survey_admin',
            ],
            [
                'name' => 'audit_manager',
                'dni' => '10000009',
                'fullname' => 'Roberto Auditoría',
                'email' => 'audit.manager@techproc.com',
                'phone' => '+51900000009',
                'password' => Hash::make('password123'),
                'role' => 'audit_manager',
            ],
            [
                'name' => 'auditor',
                'dni' => '10000010',
                'fullname' => 'Carmen Auditora',
                'email' => 'auditor@techproc.com',
                'phone' => '+51900000010',
                'password' => Hash::make('password123'),
                'role' => 'auditor',
            ],

            // ========================================
            // GRUPO QUEZADA - RECURSOS HUMANOS Y FINANZAS
            // ========================================
            [
                'name' => 'human_resources',
                'dni' => '10000011',
                'fullname' => 'Rosa Recursos Humanos',
                'email' => 'human.resources@techproc.com',
                'phone' => '+51900000011',
                'password' => Hash::make('password123'),
                'role' => 'human_resources',
            ],
            [
                'name' => 'financial_manager',
                'dni' => '10000012',
                'fullname' => 'Miguel Finanzas',
                'email' => 'financial.manager@techproc.com',
                'phone' => '+51900000012',
                'password' => Hash::make('password123'),
                'role' => 'financial_manager',
            ],
            [
                'name' => 'system_viewer',
                'dni' => '10000013',
                'fullname' => 'Sofía Visor',
                'email' => 'system.viewer@techproc.com',
                'phone' => '+51900000013',
                'password' => Hash::make('password123'),
                'role' => 'system_viewer',
            ],
            [
                'name' => 'enrollment_manager',
                'dni' => '10000014',
                'fullname' => 'Diego Matrículas',
                'email' => 'enrollment.manager@techproc.com',
                'phone' => '+51900000014',
                'password' => Hash::make('password123'),
                'role' => 'enrollment_manager',
            ],
            [
                'name' => 'data_analyst',
                'dni' => '10000015',
                'fullname' => 'Elena Datos',
                'email' => 'data.analyst@techproc.com',
                'phone' => '+51900000015',
                'password' => Hash::make('password123'),
                'role' => 'data_analyst',
            ],

            // ========================================
            // GRUPO HURTADO - MARKETING
            // ========================================
            [
                'name' => 'marketing',
                'dni' => '10000016',
                'fullname' => 'Javier Marketing',
                'email' => 'marketing@techproc.com',
                'phone' => '+51900000016',
                'password' => Hash::make('password123'),
                'role' => 'marketing',
            ],
            [
                'name' => 'marketing_admin',
                'dni' => '10000017',
                'fullname' => 'Patricia Marketing Admin',
                'email' => 'marketing.admin@techproc.com',
                'phone' => '+51900000017',
                'password' => Hash::make('password123'),
                'role' => 'marketing_admin',
            ],

            // ========================================
            // GRUPO VÁSQUEZ - ACADÉMICO
            // ========================================
            [
                'name' => 'teacher',
                'dni' => '10000018',
                'fullname' => 'Fernando Profesor',
                'email' => 'teacher@techproc.com',
                'phone' => '+51900000018',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
            ],
            [
                'name' => 'student',
                'dni' => '10000019',
                'fullname' => 'Daniela Estudiante',
                'email' => 'student@techproc.com',
                'phone' => '+51900000019',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],

            // ========================================
            // GRUPO DE LEYTON - TUTORÍAS Y ADMINISTRACIÓN
            // ========================================
            [
                'name' => 'tutor',
                'dni' => '10000020',
                'fullname' => 'Andrea Tutora',
                'email' => 'tutor@techproc.com',
                'phone' => '+51900000020',
                'password' => Hash::make('password123'),
                'role' => 'tutor',
            ],
            [
                'name' => 'administrative_clerk',
                'dni' => '10000021',
                'fullname' => 'Ricardo Administrativo',
                'email' => 'administrative.clerk@techproc.com',
                'phone' => '+51900000021',
                'password' => Hash::make('password123'),
                'role' => 'administrative_clerk',
            ],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            // Verificar si el usuario ya existe
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser) {
                $this->command->warn("⚠️  Usuario {$userData['email']} ya existe, saltando...");
                $skippedCount++;
                continue;
            }

            $user = User::create($userData);
            $user->assignRole($role);
            $createdCount++;
        }

        $this->command->info('✅ Proceso completado!');
        $this->command->info('');
        $this->command->info('Usuarios creados: ' . $createdCount);
        $this->command->info('Usuarios omitidos (ya existían): ' . $skippedCount);
        $this->command->info('Total de usuarios en el seeder: ' . count($users));
        $this->command->info('');
        $this->command->info('👤 GRUPO 03 - SOPORTE Y ADMINISTRACIÓN: 7 usuarios');
        $this->command->info('👤 GRUPO 06 - AUDITORÍA Y ENCUESTAS: 3 usuarios');
        $this->command->info('👤 GRUPO QUEZADA - RECURSOS HUMANOS Y FINANZAS: 5 usuarios');
        $this->command->info('👤 GRUPO HURTADO - MARKETING: 2 usuarios');
        $this->command->info('👤 GRUPO VÁSQUEZ - ACADÉMICO: 2 usuarios');
        $this->command->info('👤 GRUPO DE LEYTON - TUTORÍAS Y ADMINISTRACIÓN: 2 usuarios');
        $this->command->info('');
        $this->command->info('📧 Todos los usuarios tienen la contraseña: password123');
        $this->command->info('📧 Formato de email: {rol}@techproc.com');
    }
}
