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
        Schema::table('users', function (Blueprint $table) {
            // Primero eliminar el índice único del recovery_email
            $table->dropUnique('users_recovery_email_unique');
        });

        // Renombrar las columnas
        DB::statement('ALTER TABLE users RENAME COLUMN recovery_email TO secondary_email');
        DB::statement('ALTER TABLE users RENAME COLUMN recovery_email_verified_at TO secondary_email_verified_at');
        DB::statement('ALTER TABLE users RENAME COLUMN recovery_verification_code TO secondary_email_verification_code');
        DB::statement('ALTER TABLE users RENAME COLUMN recovery_code_expires_at TO secondary_email_code_expires_at');

        Schema::table('users', function (Blueprint $table) {
            // Recrear el índice único con el nuevo nombre
            $table->unique('secondary_email', 'users_secondary_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar el índice único
            $table->dropUnique('users_secondary_email_unique');
        });

        // Revertir los nombres de las columnas
        DB::statement('ALTER TABLE users RENAME COLUMN secondary_email TO recovery_email');
        DB::statement('ALTER TABLE users RENAME COLUMN secondary_email_verified_at TO recovery_email_verified_at');
        DB::statement('ALTER TABLE users RENAME COLUMN secondary_email_verification_code TO recovery_verification_code');
        DB::statement('ALTER TABLE users RENAME COLUMN secondary_email_code_expires_at TO recovery_code_expires_at');

        Schema::table('users', function (Blueprint $table) {
            // Recrear el índice único con el nombre original
            $table->unique('recovery_email', 'users_recovery_email_unique');
        });
    }
};
