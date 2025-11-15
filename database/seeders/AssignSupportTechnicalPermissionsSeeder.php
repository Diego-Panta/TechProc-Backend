<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignSupportTechnicalPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get administrative/support roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $supportRole = Role::firstOrCreate(['name' => 'support']);
        
        // Get all regular user roles (all roles except admin, super_admin, and support)
        $regularRoles = Role::whereNotIn('name', ['admin', 'super_admin', 'support'])->get();

        // Tickets permissions
        $ticketPermissions = [
            'tickets.view-any',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
        ];

        // Ticket Replies permissions
        $replyPermissions = [
            'ticket-replies.create',
            'ticket-replies.update',
            'ticket-replies.delete',
        ];

        // Reply Attachments permissions
        $attachmentPermissions = [
            'reply-attachments.delete',
        ];

        // Assign permissions to super_admin (all permissions)
        foreach (array_merge($ticketPermissions, $replyPermissions, $attachmentPermissions) as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $superAdminRole->givePermissionTo($perm);
        }

        // Assign permissions to admin (all except statistics)
        foreach (array_merge($ticketPermissions, $replyPermissions, $attachmentPermissions) as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $adminRole->givePermissionTo($perm);
        }

        // Assign permissions to support (all permissions)
        foreach (array_merge($ticketPermissions, $replyPermissions, $attachmentPermissions) as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            $supportRole->givePermissionTo($perm);
        }

        // Assign basic permissions to all regular users (only create and view own tickets)
        $regularUserPermissions = [
            'tickets.view',
            'tickets.create',
            'ticket-replies.create',
        ];

        foreach ($regularRoles as $role) {
            foreach ($regularUserPermissions as $permission) {
                $perm = Permission::firstOrCreate(['name' => $permission]);
                $role->givePermissionTo($perm);
            }
        }

        $this->command->info('✓ Permisos del módulo SupportTechnical asignados correctamente');
        $this->command->info('  - super_admin: Todos los permisos');
        $this->command->info('  - admin: Todos los permisos');
        $this->command->info('  - support: Todos los permisos');
        $this->command->info('  - Roles regulares (' . $regularRoles->count() . '): tickets.view, tickets.create, ticket-replies.create');
    }
}
