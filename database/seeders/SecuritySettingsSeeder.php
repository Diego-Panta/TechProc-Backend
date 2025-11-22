<?php

namespace Database\Seeders;

use App\Domains\Security\Models\SecuritySetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SecuritySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPermissions();
        $this->assignPermissionsToRoles();
        $this->createSettings();
    }

    /**
     * Create security permissions
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Permisos de bloqueos de usuarios
            'user-blocks.view-any',
            'user-blocks.create',
            'user-blocks.delete',

            // Permisos de configuración de seguridad
            'security-settings.view',
            'security-settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Permisos de seguridad creados correctamente.');
    }

    /**
     * Assign permissions to security and admin roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Permisos para el rol security
        $securityPermissions = [
            // Bloqueos
            'user-blocks.view-any',
            'user-blocks.create',
            'user-blocks.delete',
            // Configuración
            'security-settings.view',
            'security-settings.update',
            // Sesiones (ya existentes en el vendor)
            'sessions.view-any',
            'sessions.terminate-any',
            // Eventos (ya existentes en el vendor)
            'security-events.view-any',
        ];

        // Permisos para el rol admin (todos los de security + más)
        $adminPermissions = $securityPermissions;

        // Asignar al rol security
        $securityRole = Role::where('name', 'security')->first();
        if ($securityRole) {
            foreach ($securityPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$securityRole->hasPermissionTo($permissionName)) {
                    $securityRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Permisos asignados al rol security.');
        } else {
            $this->command->warn('Rol security no encontrado. Ejecuta primero el seeder de usuarios.');
        }

        // Asignar al rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($adminPermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$adminRole->hasPermissionTo($permissionName)) {
                    $adminRole->givePermissionTo($permission);
                }
            }
            $this->command->info('Permisos asignados al rol admin.');
        } else {
            $this->command->warn('Rol admin no encontrado. Ejecuta primero el seeder de usuarios.');
        }
    }

    /**
     * Create security settings
     */
    private function createSettings(): void
    {
        $settings = [
            // Configuración de login y bloqueo
            [
                'key' => 'max_failed_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Número máximo de intentos fallidos de login antes de bloquear al usuario',
                'group' => 'login',
            ],
            [
                'key' => 'failed_login_window_minutes',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Ventana de tiempo (en minutos) para contar los intentos fallidos de login',
                'group' => 'login',
            ],
            [
                'key' => 'block_duration_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Duración del bloqueo automático (en minutos) cuando se exceden los intentos fallidos',
                'group' => 'blocking',
            ],
            // Configuración de sesiones
            [
                'key' => 'session_timeout_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Tiempo de inactividad (en minutos) antes de considerar una sesión como inactiva',
                'group' => 'sessions',
            ],
            [
                'key' => 'max_concurrent_sessions',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Número máximo de sesiones concurrentes permitidas por usuario',
                'group' => 'sessions',
            ],
            // Configuración de detección de anomalías
            [
                'key' => 'detect_multiple_ips',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Habilitar detección de logins desde múltiples IPs en poco tiempo',
                'group' => 'anomaly_detection',
            ],
            [
                'key' => 'multiple_ip_window_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Ventana de tiempo (en minutos) para detectar logins desde múltiples IPs',
                'group' => 'anomaly_detection',
            ],
        ];

        foreach ($settings as $setting) {
            SecuritySetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Configuraciones de seguridad creadas correctamente.');
    }
}
