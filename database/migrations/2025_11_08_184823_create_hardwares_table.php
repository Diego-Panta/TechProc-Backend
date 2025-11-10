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
        Schema::create('hardwares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('tech_assets')->onDelete('cascade');
            $table->string('model');
            $table->string('serial_number')->unique();
            $table->date('warranty_expiration')->nullable();
            $table->text('specs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardwares');
    }
};
