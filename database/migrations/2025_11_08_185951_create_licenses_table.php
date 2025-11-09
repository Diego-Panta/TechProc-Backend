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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('software_id')->constrained('softwares')->onDelete('cascade');
            $table->string('key_code')->unique();
            $table->string('provider')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->decimal('cost', 10, 2);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
