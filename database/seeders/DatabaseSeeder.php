<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // 1. Tablas básicas sin dependencias
            DepartmentsSeeder::class,
            PositionsSeeder::class,
            UsersSeeder::class,
            CompaniesSeeder::class,
            AcademicPeriodsSeeder::class,
            CoursesSeeder::class,
            SubjectsSeeder::class,
            PaymentMethodsSeeder::class,
            RevenueSourcesSeeder::class,
            AccountsSeeder::class,
            
            // 2. Tablas que dependen de users
            InstructorsSeeder::class,
            EmployeesSeeder::class,
            
            // 3. Tablas académicas que dependen de las anteriores
            CourseOfferingsSeeder::class,
            GroupsSeeder::class,
            EvaluationsSeeder::class,
            
            // 4. Tablas de estudiantes
            StudentsSeeder::class,
            EnrollmentsSeeder::class,
            EnrollmentDetailsSeeder::class,
            
            // 5. Tablas de grupos y clases
            GroupParticipantsSeeder::class,
            ClassesSeeder::class,
            
            // 6. Tablas principales solicitadas
            AttendancesSeeder::class,
            GradeRecordsSeeder::class,
            FinalGradesSeeder::class,
            InvoicesSeeder::class,
            PaymentsSeeder::class,
            FinancialTransactionsSeeder::class,
            TicketsSeeder::class,
            EscalationsSeeder::class,
            SecurityLogsSeeder::class,
            BlockedIPsSeeder::class,
            SecurityAlertsSeeder::class,
            IncidentsSeeder::class,
        ]);
    }
}
