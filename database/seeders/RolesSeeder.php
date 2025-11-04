<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // CREAR ROLES
        // ========================================

        // ROL: Super Admin (tiene todos los permisos)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // ========================================
        // GRUPO 03 - SOPORTE Y ADMINISTRACIÓN
        // ========================================

        // ROL: Admin (gestión completa de usuarios, roles y permisos)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            // Usuarios
            'users.view',
            'users.view-any',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-roles',
            'users.assign-permissions',
            // Roles
            'roles.view',
            'roles.view-any',
            'roles.create',
            'roles.update',
            'roles.assign-permissions',
            // Permisos
            'permissions.view',
            'permissions.view-any',
        ]);

        // ROL: Support (soporte técnico - gestión de usuarios y tickets)
        $support = Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web']);
        $support->givePermissionTo([
            'users.view',
            'users.view-any',
            'users.update',
            'tickets.view',
            'tickets.view-any',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'ticket-replies.view',
            'ticket-replies.view-any',
            'ticket-replies.create',
            'ticket-replies.update',
            'ticket-replies.delete',
            'tech-assets.view',
            'tech-assets.view-any',
        ]);

        // ROL: Infrastructure
        $infrastructure = Role::firstOrCreate(['name' => 'infrastructure', 'guard_name' => 'web']);
        $infrastructure->givePermissionTo([
            'tech-assets.view',
            'tech-assets.view-any',
            'tech-assets.create',
            'tech-assets.update',
            'tech-assets.delete',
            'tickets.view',
            'tickets.view-any',
        ]);

        // ROL: Security
        $security = Role::firstOrCreate(['name' => 'security', 'guard_name' => 'web']);
        $security->givePermissionTo([
            'users.view',
            'users.view-any',
            'roles.view',
            'roles.view-any',
            'permissions.view',
            'permissions.view-any',
            'audits.view',
            'audits.view-any',
        ]);

        // ROL: Academic Analyst
        $academicAnalyst = Role::firstOrCreate(['name' => 'academic_analyst', 'guard_name' => 'web']);
        $academicAnalyst->givePermissionTo([
            'courses.view',
            'courses.view-any',
            'enrollments.view',
            'enrollments.view-any',
            'grades.view',
            'grades.view-any',
            'attendances.view',
            'attendances.view-any',
        ]);

        // ROL: Web
        $web = Role::firstOrCreate(['name' => 'web', 'guard_name' => 'web']);
        $web->givePermissionTo([
            'strategic-contents.view',
            'strategic-contents.view-any',
            'strategic-contents.create',
            'strategic-contents.update',
            'strategic-contents.delete',
            'strategic-documents.view',
            'strategic-documents.view-any',
            'strategic-documents.create',
            'strategic-documents.update',
            'strategic-documents.delete',
        ]);

        // ========================================
        // GRUPO 06 - AUDITORÍA Y ENCUESTAS
        // ========================================

        // ROL: Survey Admin
        $surveyAdmin = Role::firstOrCreate(['name' => 'survey_admin', 'guard_name' => 'web']);
        $surveyAdmin->givePermissionTo([
            'surveys.view',
            'surveys.view-any',
            'surveys.create',
            'surveys.update',
            'surveys.delete',
            'survey-questions.view',
            'survey-questions.view-any',
            'survey-questions.create',
            'survey-questions.update',
            'survey-questions.delete',
            'survey-responses.view',
            'survey-responses.view-any',
            'response-details.view',
            'response-details.view-any',
            'survey-mappings.view',
            'survey-mappings.view-any',
            'survey-mappings.create',
            'survey-mappings.update',
            'survey-mappings.delete',
        ]);

        // ROL: Audit Manager
        $auditManager = Role::firstOrCreate(['name' => 'audit_manager', 'guard_name' => 'web']);
        $auditManager->givePermissionTo([
            'audits.view',
            'audits.view-any',
            'audits.create',
            'audits.update',
            'audits.delete',
            'audit-findings.view',
            'audit-findings.view-any',
            'audit-findings.create',
            'audit-findings.update',
            'audit-findings.delete',
            'finding-evidences.view',
            'finding-evidences.view-any',
            'finding-evidences.create',
            'finding-evidences.update',
            'finding-evidences.delete',
            'audit-actions.view',
            'audit-actions.view-any',
            'audit-actions.create',
            'audit-actions.update',
            'audit-actions.delete',
        ]);

        // ROL: Auditor (solo lectura de todo)
        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->givePermissionTo([
            'users.view',
            'users.view-any',
            'roles.view',
            'roles.view-any',
            'permissions.view',
            'permissions.view-any',
            'audits.view',
            'audits.view-any',
            'audit-findings.view',
            'audit-findings.view-any',
            'finding-evidences.view',
            'finding-evidences.view-any',
        ]);

        // ========================================
        // GRUPO QUEZADA - RECURSOS HUMANOS Y FINANZAS
        // ========================================

        // ROL: Human Resources
        $humanResources = Role::firstOrCreate(['name' => 'human_resources', 'guard_name' => 'web']);
        $humanResources->givePermissionTo([
            'contracts.view',
            'contracts.view-any',
            'contracts.create',
            'contracts.update',
            'contracts.delete',
            'payroll-expenses.view',
            'payroll-expenses.view-any',
            'payroll-expenses.create',
            'payroll-expenses.update',
            'payroll-expenses.delete',
            'offers.view',
            'offers.view-any',
            'offers.create',
            'offers.update',
            'offers.delete',
            'applications.view',
            'applications.view-any',
            'applications.create',
            'applications.update',
            'applications.delete',
        ]);

        // ROL: Financial Manager
        $financialManager = Role::firstOrCreate(['name' => 'financial_manager', 'guard_name' => 'web']);
        $financialManager->givePermissionTo([
            'enrollment-payments.view',
            'enrollment-payments.view-any',
            'enrollment-payments.create',
            'enrollment-payments.update',
            'enrollment-payments.delete',
            'payroll-expenses.view',
            'payroll-expenses.view-any',
        ]);

        // ROL: System Viewer
        $systemViewer = Role::firstOrCreate(['name' => 'system_viewer', 'guard_name' => 'web']);
        $systemViewer->givePermissionTo([
            'users.view',
            'users.view-any',
            'courses.view',
            'courses.view-any',
            'enrollments.view',
            'enrollments.view-any',
        ]);

        // ROL: Enrollment Manager
        $enrollmentManager = Role::firstOrCreate(['name' => 'enrollment_manager', 'guard_name' => 'web']);
        $enrollmentManager->givePermissionTo([
            'enrollments.view',
            'enrollments.view-any',
            'enrollments.create',
            'enrollments.update',
            'enrollments.delete',
            'enrollment-results.view',
            'enrollment-results.view-any',
            'enrollment-results.create',
            'enrollment-results.update',
            'enrollment-results.delete',
            'certificates.view',
            'certificates.view-any',
            'certificates.create',
            'certificates.update',
            'certificates.delete',
        ]);

        // ROL: Data Analyst
        $dataAnalyst = Role::firstOrCreate(['name' => 'data_analyst', 'guard_name' => 'web']);
        $dataAnalyst->givePermissionTo([
            'users.view',
            'users.view-any',
            'courses.view',
            'courses.view-any',
            'enrollments.view',
            'enrollments.view-any',
            'grades.view',
            'grades.view-any',
            'attendances.view',
            'attendances.view-any',
            'survey-responses.view',
            'survey-responses.view-any',
            'response-details.view',
            'response-details.view-any',
        ]);

        // ========================================
        // GRUPO HURTADO - MARKETING
        // ========================================

        // ROL: Marketing
        $marketing = Role::firstOrCreate(['name' => 'marketing', 'guard_name' => 'web']);
        $marketing->givePermissionTo([
            'strategic-contents.view',
            'strategic-contents.view-any',
            'strategic-contents.create',
            'strategic-contents.update',
            'organizations.view',
            'organizations.view-any',
        ]);

        // ROL: Marketing Admin
        $marketingAdmin = Role::firstOrCreate(['name' => 'marketing_admin', 'guard_name' => 'web']);
        $marketingAdmin->givePermissionTo([
            'strategic-contents.view',
            'strategic-contents.view-any',
            'strategic-contents.create',
            'strategic-contents.update',
            'strategic-contents.delete',
            'strategic-documents.view',
            'strategic-documents.view-any',
            'strategic-documents.create',
            'strategic-documents.update',
            'strategic-documents.delete',
            'organizations.view',
            'organizations.view-any',
            'organizations.create',
            'organizations.update',
            'organizations.delete',
            'agreements.view',
            'agreements.view-any',
            'agreements.create',
            'agreements.update',
            'agreements.delete',
        ]);

        // ========================================
        // GRUPO VÁSQUEZ - ACADÉMICO
        // ========================================

        // ROL: Teacher (profesor - gestión de sus estudiantes)
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->givePermissionTo([
            'users.view',
            'users.view-any',
            'courses.view',
            'courses.view-any',
            'modules.view',
            'modules.view-any',
            'modules.create',
            'modules.update',
            'modules.delete',
            'groups.view',
            'groups.view-any',
            'class-sessions.view',
            'class-sessions.view-any',
            'class-sessions.create',
            'class-sessions.update',
            'class-sessions.delete',
            'class-session-materials.view',
            'class-session-materials.view-any',
            'class-session-materials.create',
            'class-session-materials.update',
            'class-session-materials.delete',
            'exams.view',
            'exams.view-any',
            'exams.create',
            'exams.update',
            'exams.delete',
            'grades.view',
            'grades.view-any',
            'grades.create',
            'grades.update',
            'attendances.view',
            'attendances.view-any',
            'attendances.create',
            'attendances.update',
        ]);

        // ROL: Student (estudiante - acceso básico)
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->givePermissionTo([
            'courses.view',
            'courses.view-any',
            'modules.view',
            'modules.view-any',
            'class-sessions.view',
            'class-sessions.view-any',
            'class-session-materials.view',
            'class-session-materials.view-any',
            'exams.view',
            'exams.view-any',
            'grades.view',
            'attendances.view',
            'forums.view',
            'forums.view-any',
            'threads.view',
            'threads.view-any',
            'threads.create',
            'comments.view',
            'comments.view-any',
            'comments.create',
            'votes.create',
            'tickets.view',
            'tickets.view-any',
            'tickets.create',
            'tickets.update',
        ]);
        
        // ========================================
        // GRUPO DE LEYTON - TUTORÍAS Y ADMINISTRACIÓN
        // ========================================

        // ROL: Tutor (instructor/profesor/psicólogo - manejo de tutorías)
        $tutor = Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);

        // ROL: Administrative Clerk (empleado administrativo - trámites documentarios)
        $administrativeClerk = Role::firstOrCreate(['name' => 'administrative_clerk', 'guard_name' => 'web']);

        $this->command->info('✅ Roles creados exitosamente!');
        $this->command->info('');
        $this->command->info('Roles creados:');
        $this->command->info('  🔧 GRUPO 03 - SOPORTE Y ADMINISTRACIÓN:');
        $this->command->info('    - super_admin (todos los permisos)');
        $this->command->info('    - admin (gestión completa)');
        $this->command->info('    - support (soporte técnico)');
        $this->command->info('    - infrastructure (infraestructura)');
        $this->command->info('    - security (seguridad)');
        $this->command->info('    - academic_analyst (analista académico)');
        $this->command->info('    - web (gestión web)');
        $this->command->info('');
        $this->command->info('  📊 GRUPO 06 - AUDITORÍA Y ENCUESTAS:');
        $this->command->info('    - survey_admin (administrador de encuestas)');
        $this->command->info('    - audit_manager (gestor de auditorías)');
        $this->command->info('    - auditor (solo lectura)');
        $this->command->info('');
        $this->command->info('  💼 GRUPO QUEZADA - RECURSOS HUMANOS Y FINANZAS:');
        $this->command->info('    - human_resources (recursos humanos)');
        $this->command->info('    - financial_manager (gestor financiero)');
        $this->command->info('    - system_viewer (visor del sistema)');
        $this->command->info('    - enrollment_manager (gestor de inscripciones)');
        $this->command->info('    - data_analyst (analista de datos)');
        $this->command->info('');
        $this->command->info('  📢 GRUPO HURTADO - MARKETING:');
        $this->command->info('    - marketing (marketing)');
        $this->command->info('    - marketing_admin (administrador de marketing)');
        $this->command->info('');
        $this->command->info('  🎓 GRUPO VÁSQUEZ - ACADÉMICO:');
        $this->command->info('    - teacher (profesor)');
        $this->command->info('    - student (estudiante)');
        $this->command->info('');
        $this->command->info('  🎯 GRUPO DE LEYTON - TUTORÍAS Y ADMINISTRACIÓN:');
        $this->command->info('    - tutor (instructor/profesor/psicólogo)');
        $this->command->info('    - administrative_clerk (empleado administrativo)');
        $this->command->info('');
        $this->command->info('Total de roles: ' . Role::count());
    }
}
