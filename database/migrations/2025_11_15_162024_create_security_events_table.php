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
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type'); // login_success, login_failed, token_created, token_revoked, session_terminated, password_reset_requested, 2fa_enabled, 2fa_disabled
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable(); // Datos adicionales del evento
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('event_type');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
