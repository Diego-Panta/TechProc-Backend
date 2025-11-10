<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('recovery_email')->nullable()->after('email');
            $table->timestamp('recovery_email_verified_at')->nullable()->after('recovery_email');
            $table->string('recovery_verification_code', 6)->nullable()->after('recovery_email_verified_at');
            $table->timestamp('recovery_code_expires_at')->nullable()->after('recovery_verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'recovery_email',
                'recovery_email_verified_at',
                'recovery_verification_code',
                'recovery_code_expires_at'
            ]);
        });
    }
};
