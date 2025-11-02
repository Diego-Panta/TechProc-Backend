<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // PERMISOS DE USUARIOS
        // ========================================
        $userPermissions = [
            'users.view',           // Ver lista de usuarios
            'users.view-any',       // Ver cualquier usuario
            'users.create',         // Crear usuarios
            'users.update',         // Actualizar usuarios
            'users.delete',         // Eliminar usuarios
            'users.assign-roles',   // Asignar roles a usuarios
            'users.assign-permissions', // Asignar permisos a usuarios
        ];

        foreach ($userPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // ========================================
        // PERMISOS DE ROLES
        // ========================================
        $rolePermissions = [
            'roles.view',           // Ver lista de roles
            'roles.view-any',       // Ver cualquier rol
            'roles.create',         // Crear roles
            'roles.update',         // Actualizar roles
            'roles.delete',         // Eliminar roles
            'roles.assign-permissions', // Asignar permisos a roles
        ];

        foreach ($rolePermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // ========================================
        // PERMISOS DE PERMISOS
        // ========================================
        $permissionPermissions = [
            'permissions.view',     // Ver lista de permisos
            'permissions.view-any', // Ver cualquier permiso
            'permissions.create',   // Crear permisos
            'permissions.update',   // Actualizar permisos
            'permissions.delete',   // Eliminar permisos
        ];

        foreach ($permissionPermissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // ========================================
        // CREAR ROLES
        // ========================================

        // ROL: Super Admin (tiene todos los permisos)
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // ROL: Admin (gestión completa de usuarios, roles y permisos)
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
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

        // ROL: Teacher (profesor - gestión de sus estudiantes)
        $teacher = Role::create(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->givePermissionTo([
            'users.view',       // Ver lista de usuarios (sus estudiantes)
            'users.view-any',   // Ver detalles de usuarios
        ]);

        // ROL: Student (estudiante - acceso básico)
        $student = Role::create(['name' => 'student', 'guard_name' => 'web']);
        // Sin permisos especiales, solo acceso al sistema

        // ROL: Support (soporte técnico - gestión de usuarios y tickets)
        $support = Role::create(['name' => 'support', 'guard_name' => 'web']);
        $support->givePermissionTo([
            'users.view',
            'users.view-any',
            'users.update',     // Puede actualizar usuarios (resetear contraseñas, etc)
        ]);

        // ROL: Auditor (auditor - solo lectura de todo)
        $auditor = Role::create(['name' => 'auditor', 'guard_name' => 'web']);
        $auditor->givePermissionTo([
            'users.view',
            'users.view-any',
            'roles.view',
            'roles.view-any',
            'permissions.view',
            'permissions.view-any',
        ]);

        $this->command->info('✅ Roles y permisos creados exitosamente!');
        $this->command->info('');
        $this->command->info('Roles creados:');
        $this->command->info('  - super_admin (todos los permisos)');
        $this->command->info('  - admin (gestión completa)');
        $this->command->info('  - teacher (gestión de estudiantes)');
        $this->command->info('  - student (acceso básico)');
        $this->command->info('  - support (soporte técnico)');
        $this->command->info('  - auditor (solo lectura)');
        $this->command->info('');
        $this->command->info('Total de permisos creados: ' . Permission::count());
    }
}
