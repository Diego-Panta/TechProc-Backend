<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionsToAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🔄 Buscando el rol admin...');

        // Obtener el rol admin
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('❌ El rol "admin" no existe. Por favor, créalo primero.');
            return;
        }

        $this->command->info('✅ Rol admin encontrado!');
        $this->command->info('');

        // Obtener TODOS los permisos de la base de datos
        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            $this->command->error('❌ No hay permisos en la base de datos. Ejecuta primero el PermissionsSeeder.');
            return;
        }

        $this->command->info('🔄 Asignando ' . $allPermissions->count() . ' permisos al rol admin...');
        $this->command->info('');

        // Asignar TODOS los permisos al rol admin
        $adminRole->syncPermissions($allPermissions);

        $this->command->info('✅ Todos los permisos han sido asignados exitosamente al rol admin!');
        $this->command->info('');
        $this->command->info('📊 Resumen:');
        $this->command->info('   - Rol: admin');
        $this->command->info('   - Total de permisos asignados: ' . $allPermissions->count());
        $this->command->info('');

        // Mostrar algunos permisos como ejemplo
        $this->command->info('📝 Algunos permisos asignados:');
        $samplePermissions = $allPermissions->take(10);
        foreach ($samplePermissions as $permission) {
            $this->command->info('   ✓ ' . $permission->name);
        }
        $this->command->info('   ... y ' . ($allPermissions->count() - 10) . ' más.');
    }
}
