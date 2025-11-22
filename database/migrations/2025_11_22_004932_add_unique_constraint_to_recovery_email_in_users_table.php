<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, limpiar duplicados
        // Mantener solo el recovery_email del usuario más antiguo (menor ID)
        // y limpiar el resto
        DB::statement("
            UPDATE users u1
            LEFT JOIN (
                SELECT MIN(id) as min_id, recovery_email
                FROM users
                WHERE recovery_email IS NOT NULL
                GROUP BY recovery_email
            ) u2 ON u1.recovery_email = u2.recovery_email AND u1.id = u2.min_id
            SET u1.recovery_email = NULL,
                u1.recovery_email_verified_at = NULL,
                u1.recovery_verification_code = NULL,
                u1.recovery_code_expires_at = NULL
            WHERE u1.recovery_email IS NOT NULL
            AND u2.min_id IS NULL
        ");

        Schema::table('users', function (Blueprint $table) {
            // Agregar índice único a recovery_email
            $table->unique('recovery_email', 'users_recovery_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índice único
            $table->dropUnique('users_recovery_email_unique');
        });
    }
};
