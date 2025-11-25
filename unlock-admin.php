<?php
/**
 * Script para desbloquear usuario admin@incadev.com y cambiar contraseña
 *
 * Base de datos: ixocakuy_lms_database@instituto.cetivirgendelapuerta.com
 *
 * Uso:
 * php unlock-admin.php
 */

// Cargar Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domains\AuthenticationSessions\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use IncadevUns\CoreDomain\Models\UserBlock;

echo "==============================================\n";
echo "  DESBLOQUEAR Y RESETEAR CONTRASEÑA ADMIN\n";
echo "==============================================\n\n";

try {
    // Verificar conexión a la base de datos
    echo "🔌 Verificando conexión a la base de datos...\n";
    echo "   Host: " . env('DB_HOST') . "\n";
    echo "   Database: " . env('DB_DATABASE') . "\n\n";

    try {
        DB::connection()->getPdo();
        echo "   ✅ Conexión exitosa\n\n";
    } catch (\Exception $e) {
        echo "   ❌ Error de conexión: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Email del admin
    $email = 'admin@incadev.com';

    // Nueva contraseña (fácil de recordar)
    $newPassword = 'Admin123!';

    echo "🔍 Buscando usuario: $email\n\n";

    // Buscar usuario
    $user = User::where('email', $email)->first();

    if (!$user) {
        echo "❌ ERROR: Usuario '$email' no encontrado\n\n";
        echo "💡 Creando usuario admin...\n\n";

        // Crear usuario admin
        $user = User::create([
            'name' => 'Administrador',
            'email' => $email,
            'password' => Hash::make($newPassword),
            'email_verified_at' => now(),
        ]);

        echo "✅ Usuario admin creado exitosamente\n\n";
    } else {
        echo "✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n\n";
    }

    // Información actual del usuario
    echo "📋 INFORMACIÓN ACTUAL:\n";
    echo "─────────────────────────────────────────────\n";
    echo "   ID: {$user->id}\n";
    echo "   Nombre: {$user->name}\n";
    echo "   Email: {$user->email}\n";
    echo "   Email verificado: " . ($user->email_verified_at ? 'Sí' : 'No') . "\n";
    echo "   Creado: {$user->created_at}\n";
    echo "\n";

    // Desbloquear usuario
    echo "🔓 Desbloqueando usuario...\n\n";

    // Eliminar tokens de reseteo de contraseña
    try {
        $deleted = DB::table('password_reset_tokens')->where('email', $email)->delete();
        if ($deleted > 0) {
            echo "   ✅ $deleted token(s) de reseteo eliminados\n";
        } else {
            echo "   ✓ No hay tokens de reseteo\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠️  Tabla password_reset_tokens no existe\n";
    }

    // Limpiar sesiones activas
    try {
        $deleted = DB::table('sessions')->where('user_id', $user->id)->delete();
        if ($deleted > 0) {
            echo "   ✅ $deleted sesión(es) activa(s) eliminadas\n";
        } else {
            echo "   ✓ No hay sesiones activas\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠️  No se pudieron limpiar sesiones: " . $e->getMessage() . "\n";
    }

    // Limpiar tokens de acceso personal (Sanctum)
    try {
        $deleted = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\Domains\AuthenticationSessions\Models\User')
            ->where('tokenable_id', $user->id)
            ->delete();
        if ($deleted > 0) {
            echo "   ✅ $deleted token(s) de acceso eliminados\n";
        } else {
            echo "   ✓ No hay tokens de acceso\n";
        }
    } catch (\Exception $e) {
        echo "   ⚠️  No se pudieron limpiar tokens: " . $e->getMessage() . "\n";
    }

    // ⚠️ CRÍTICO: Desbloquear usuario en tabla user_blocks
    echo "\n🚨 Verificando bloqueos en tabla user_blocks...\n\n";

    try {
        // Buscar bloqueos activos del usuario
        $activeBlocks = UserBlock::forUser($user->id)
            ->currentlyBlocked()
            ->get();

        if ($activeBlocks->count() > 0) {
            echo "   ⚠️  ENCONTRADOS {$activeBlocks->count()} BLOQUEO(S) ACTIVO(S):\n\n";

            foreach ($activeBlocks as $block) {
                echo "      🔒 Bloqueo ID: {$block->id}\n";
                echo "         Tipo: {$block->block_type_label}\n";
                echo "         Razón: " . ($block->reason ?? 'Sin especificar') . "\n";
                echo "         Bloqueado desde: {$block->blocked_at->format('Y-m-d H:i:s')}\n";
                echo "         Bloqueado hasta: " . ($block->blocked_until ? $block->blocked_until->format('Y-m-d H:i:s') : 'Permanente') . "\n";
                echo "         Tiempo restante: {$block->remaining_time}\n\n";

                // Desactivar el bloqueo
                $block->is_active = false;
                $block->unblocked_at = now();
                $block->unblocked_by = 1; // ID del admin que desbloquea
                $block->save();

                echo "      ✅ Bloqueo ID {$block->id} DESACTIVADO\n\n";
            }

            echo "   ✅ Todos los bloqueos han sido desactivados\n";
        } else {
            echo "   ✓ No hay bloqueos activos\n";
        }

        // También desactivar bloqueos expirados pero aún marcados como activos
        $expiredBlocks = UserBlock::forUser($user->id)
            ->expired()
            ->get();

        if ($expiredBlocks->count() > 0) {
            echo "\n   🔄 Limpiando {$expiredBlocks->count()} bloqueo(s) expirado(s)...\n";
            foreach ($expiredBlocks as $block) {
                $block->is_active = false;
                $block->unblocked_at = now();
                $block->save();
            }
            echo "   ✅ Bloqueos expirados limpiados\n";
        }

    } catch (\Exception $e) {
        echo "   ❌ Error al verificar/desbloquear: " . $e->getMessage() . "\n";
        echo "   Stack: " . $e->getTraceAsString() . "\n";
    }

    echo "\n";

    // Cambiar contraseña
    echo "🔑 Actualizando contraseña y verificando email...\n\n";

    $user->password = Hash::make($newPassword);
    $user->email_verified_at = now(); // Asegurar que el email está verificado
    $user->save();

    echo "   ✅ Contraseña actualizada\n";
    echo "   ✅ Email verificado\n";
    echo "\n";

    // Asignar rol admin si no lo tiene
    echo "👑 Verificando rol de administrador...\n\n";

    try {
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
            echo "   ✅ Rol 'admin' asignado\n";
        } else {
            echo "   ✅ Usuario ya tiene rol 'admin'\n";
        }

        // Verificar permisos
        $permissions = $user->getAllPermissions()->count();
        echo "   ✅ Usuario tiene $permissions permisos\n";

    } catch (\Exception $e) {
        echo "   ⚠️  No se pudo verificar/asignar rol: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Resumen final
    echo "==============================================\n";
    echo "  ✅ PROCESO COMPLETADO EXITOSAMENTE\n";
    echo "==============================================\n\n";

    echo "📧 Email:     $email\n";
    echo "🔑 Contraseña: $newPassword\n\n";

    echo "⚠️  IMPORTANTE: Guarda estas credenciales en un lugar seguro\n\n";

    echo "🌐 URL de Login (Producción):\n";
    echo "   " . env('APP_URL') . "/api/auth/login\n\n";

    echo "📝 Ejemplo de request con cURL:\n\n";
    echo "curl -X POST " . env('APP_URL') . "/api/auth/login \\\n";
    echo "  -H \"Content-Type: application/json\" \\\n";
    echo "  -d '{\n";
    echo "    \"email\": \"$email\",\n";
    echo "    \"password\": \"$newPassword\"\n";
    echo "  }'\n\n";

    echo "==============================================\n";

    // Limpiar caché
    echo "\n🧹 Limpiando caché...\n\n";

    try {
        Artisan::call('cache:clear');
        echo "   ✅ Cache limpiado\n";

        Artisan::call('config:clear');
        echo "   ✅ Config limpiado\n";

        Artisan::call('route:clear');
        echo "   ✅ Routes limpiadas\n";

    } catch (\Exception $e) {
        echo "   ⚠️  Error limpiando caché: " . $e->getMessage() . "\n";
    }

    echo "\n";
    echo "✅ Todo listo! Puedes iniciar sesión ahora.\n\n";

    // Información adicional
    echo "==============================================\n";
    echo "  📊 INFORMACIÓN DEL SISTEMA\n";
    echo "==============================================\n\n";
    echo "   Entorno: " . env('APP_ENV') . "\n";
    echo "   URL: " . env('APP_URL') . "\n";
    echo "   Base de datos: " . env('DB_DATABASE') . "@" . env('DB_HOST') . "\n";
    echo "   Frontend: " . env('FRONTEND_URL') . "\n\n";
    echo "==============================================\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR CRÍTICO: " . $e->getMessage() . "\n\n";
    echo "Detalles del error:\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
