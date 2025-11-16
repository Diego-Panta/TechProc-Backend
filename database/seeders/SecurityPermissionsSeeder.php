<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SecurityPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos básicos (para usuarios normales - solo su propia info)
        $basicPermissions = [
            'security-dashboard.view',      // Ver MI dashboard
            'sessions.view',                // Ver MIS sesiones
            'sessions.terminate',           // Terminar MIS sesiones
            'tokens.view',                  // Ver MIS tokens
            'tokens.revoke',                // Revocar MIS tokens
            'security-events.view',         // Ver MIS eventos
        ];

        // Permisos administrativos (para rol security - info de TODOS)
        $adminPermissions = [
            'security-dashboard.view-any',  // Ver dashboard de TODOS
            'sessions.view-any',            // Ver sesiones de TODOS
            'sessions.terminate-any',       // Terminar sesiones de CUALQUIERA
            'tokens.view-any',              // Ver tokens de TODOS
            'tokens.revoke-any',            // Revocar tokens de CUALQUIERA
            'security-events.view-any',     // Ver eventos de TODOS
            'security-events.export',       // Exportar reportes
            'security-alerts.view',         // Ver alertas del sistema
            'security-alerts.resolve',      // Resolver alertas
            'security-users.view',          // Ver lista de usuarios con alertas
            'security-users.block',         // Bloquear usuarios
            'security-users.unblock',       // Desbloquear usuarios
        ];

        $allPermissions = array_merge($basicPermissions, $adminPermissions);

        // Crear permisos
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $this->command->info('Permisos de seguridad creados: ' . count($allPermissions));

        // Crear rol security si no existe
        $securityRole = Role::firstOrCreate(
            ['name' => 'security', 'guard_name' => 'web']
        );

        // Asignar TODOS los permisos al rol security (acceso global)
        $securityRole->syncPermissions($allPermissions);
        $this->command->info('✅ Rol "security" creado con acceso global a TODO');

        // Asignar TODOS los permisos al rol admin (acceso completo al módulo de seguridad)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($allPermissions);
            $this->command->info('✅ Rol "admin" tiene acceso completo al módulo de seguridad');
        }

        // Asignar TODOS los permisos al rol super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions($allPermissions);
            $this->command->info('✅ Rol "super_admin" tiene acceso completo al módulo de seguridad');
        }
    }
}
