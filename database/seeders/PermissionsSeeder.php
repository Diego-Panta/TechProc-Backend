<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = [];

        // ========================================
        // PERMISOS DE USUARIOS
        // ========================================
        $userPermissions = [
            'users.view',
            'users.view-any',
            'users.create',
            'users.update',
            'users.delete',
            'users.assign-roles',
            'users.assign-permissions',
        ];
        $allPermissions = array_merge($allPermissions, $userPermissions);

        // ========================================
        // PERMISOS DE ROLES
        // ========================================
        $rolePermissions = [
            'roles.view',
            'roles.view-any',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign-permissions',
        ];
        $allPermissions = array_merge($allPermissions, $rolePermissions);

        // ========================================
        // PERMISOS DE PERMISOS
        // ========================================
        $permissionPermissions = [
            'permissions.view',
            'permissions.view-any',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
        ];
        $allPermissions = array_merge($allPermissions, $permissionPermissions);

        // ========================================
        // PERMISOS DE PERFILES
        // ========================================
        $profilePermissions = [
            'student-profiles.view',
            'student-profiles.view-any',
            'student-profiles.create',
            'student-profiles.update',
            'student-profiles.delete',

            'teacher-profiles.view',
            'teacher-profiles.view-any',
            'teacher-profiles.create',
            'teacher-profiles.update',
            'teacher-profiles.delete',

            'support-profiles.view',
            'support-profiles.view-any',
            'support-profiles.create',
            'support-profiles.update',
            'support-profiles.delete',
        ];
        $allPermissions = array_merge($allPermissions, $profilePermissions);

        // ========================================
        // PERMISOS DE ENCUESTAS
        // ========================================
        $surveyPermissions = [
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
            'survey-responses.create',
            'survey-responses.update',
            'survey-responses.delete',

            'response-details.view',
            'response-details.view-any',
            'response-details.create',
            'response-details.update',
            'response-details.delete',

            'survey-mappings.view',
            'survey-mappings.view-any',
            'survey-mappings.create',
            'survey-mappings.update',
            'survey-mappings.delete',
        ];
        $allPermissions = array_merge($allPermissions, $surveyPermissions);

        // ========================================
        // PERMISOS DE CONTENIDO ESTRATÉGICO
        // ========================================
        $strategicPermissions = [
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
        ];
        $allPermissions = array_merge($allPermissions, $strategicPermissions);

        // ========================================
        // PERMISOS DE ORGANIZACIONES Y CONVENIOS
        // ========================================
        $organizationPermissions = [
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
        ];
        $allPermissions = array_merge($allPermissions, $organizationPermissions);

        // ========================================
        // PERMISOS DE MENSAJERÍA
        // ========================================
        $messagingPermissions = [
            'conversations.view',
            'conversations.view-any',
            'conversations.create',
            'conversations.update',
            'conversations.delete',

            'conversation-users.view',
            'conversation-users.view-any',
            'conversation-users.create',
            'conversation-users.update',
            'conversation-users.delete',

            'messages.view',
            'messages.view-any',
            'messages.create',
            'messages.update',
            'messages.delete',

            'message-files.view',
            'message-files.view-any',
            'message-files.create',
            'message-files.update',
            'message-files.delete',
        ];
        $allPermissions = array_merge($allPermissions, $messagingPermissions);

        // ========================================
        // PERMISOS ACADÉMICOS
        // ========================================
        $academicPermissions = [
            'academic-settings.view',
            'academic-settings.view-any',
            'academic-settings.create',
            'academic-settings.update',
            'academic-settings.delete',

            'courses.view',
            'courses.view-any',
            'courses.create',
            'courses.update',
            'courses.delete',

            'course-versions.view',
            'course-versions.view-any',
            'course-versions.create',
            'course-versions.update',
            'course-versions.delete',

            'modules.view',
            'modules.view-any',
            'modules.create',
            'modules.update',
            'modules.delete',

            'groups.view',
            'groups.view-any',
            'groups.create',
            'groups.update',
            'groups.delete',

            'group-teachers.view',
            'group-teachers.view-any',
            'group-teachers.create',
            'group-teachers.update',
            'group-teachers.delete',

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
        ];
        $allPermissions = array_merge($allPermissions, $academicPermissions);

        // ========================================
        // PERMISOS DE INSCRIPCIONES Y CALIFICACIONES
        // ========================================
        $enrollmentPermissions = [
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

            'attendances.view',
            'attendances.view-any',
            'attendances.create',
            'attendances.update',
            'attendances.delete',

            'grades.view',
            'grades.view-any',
            'grades.create',
            'grades.update',
            'grades.delete',

            'certificates.view',
            'certificates.view-any',
            'certificates.create',
            'certificates.update',
            'certificates.delete',
        ];
        $allPermissions = array_merge($allPermissions, $enrollmentPermissions);

        // ========================================
        // PERMISOS DE PAGOS
        // ========================================
        $paymentPermissions = [
            'enrollment-payments.view',
            'enrollment-payments.view-any',
            'enrollment-payments.create',
            'enrollment-payments.update',
            'enrollment-payments.delete',
        ];
        $allPermissions = array_merge($allPermissions, $paymentPermissions);

        // ========================================
        // PERMISOS DE RECURSOS HUMANOS
        // ========================================
        $hrPermissions = [
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
        ];
        $allPermissions = array_merge($allPermissions, $hrPermissions);

        // ========================================
        // PERMISOS DE DOCUMENTOS ADMINISTRATIVOS
        // ========================================
        $documentPermissions = [
            'administrative-documents.view',
            'administrative-documents.view-any',
            'administrative-documents.create',
            'administrative-documents.update',
            'administrative-documents.delete',
        ];
        $allPermissions = array_merge($allPermissions, $documentPermissions);

        // ========================================
        // PERMISOS DE CHATBOT
        // ========================================
        $chatbotPermissions = [
            'chatbot-faqs.view',
            'chatbot-faqs.view-any',
            'chatbot-faqs.create',
            'chatbot-faqs.update',
            'chatbot-faqs.delete',

            'chatbot-conversations.view',
            'chatbot-conversations.view-any',
            'chatbot-conversations.create',
            'chatbot-conversations.update',
            'chatbot-conversations.delete',
        ];
        $allPermissions = array_merge($allPermissions, $chatbotPermissions);

        // ========================================
        // PERMISOS DE SOPORTE TÉCNICO
        // ========================================
        $supportPermissions = [
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

            'reply-attachments.view',
            'reply-attachments.view-any',
            'reply-attachments.create',
            'reply-attachments.update',
            'reply-attachments.delete',

            'tech-assets.view',
            'tech-assets.view-any',
            'tech-assets.create',
            'tech-assets.update',
            'tech-assets.delete',

            'availabilities.view',
            'availabilities.view-any',
            'availabilities.create',
            'availabilities.update',
            'availabilities.delete',

            'appointments.view',
            'appointments.view-any',
            'appointments.create',
            'appointments.update',
            'appointments.delete',
        ];
        $allPermissions = array_merge($allPermissions, $supportPermissions);

        // ========================================
        // PERMISOS DE FOROS
        // ========================================
        $forumPermissions = [
            'forums.view',
            'forums.view-any',
            'forums.create',
            'forums.update',
            'forums.delete',

            'threads.view',
            'threads.view-any',
            'threads.create',
            'threads.update',
            'threads.delete',

            'comments.view',
            'comments.view-any',
            'comments.create',
            'comments.update',
            'comments.delete',

            'votes.view',
            'votes.view-any',
            'votes.create',
            'votes.update',
            'votes.delete',
        ];
        $allPermissions = array_merge($allPermissions, $forumPermissions);

        // ========================================
        // PERMISOS DE AUDITORÍA
        // ========================================
        $auditPermissions = [
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
        ];
        $allPermissions = array_merge($allPermissions, $auditPermissions);

        // ========================================
        // PERMISOS DE SESIONES
        // ========================================
        $sessionPermissions = [
            'sessions.view',
            'sessions.view-any',
            'sessions.create',
            'sessions.update',
            'sessions.delete',
        ];
        $allPermissions = array_merge($allPermissions, $sessionPermissions);

        // ========================================
        // CREAR TODOS LOS PERMISOS
        // ========================================
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('✅ Todos los permisos creados exitosamente!');
        $this->command->info('');
        $this->command->info('Total de permisos creados: ' . count($allPermissions));
    }
}
