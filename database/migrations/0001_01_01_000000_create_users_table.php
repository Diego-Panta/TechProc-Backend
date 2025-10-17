<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->string('role');
            $table->string('profile_photo')->nullable();
            $table->string('status')->default('inactive');
            $table->string('last_access_ip')->nullable();
            $table->timestamp('last_access')->nullable();
            $table->rememberToken();
            $table->timestamps();
            // Agregar índice para mejor performance de búsquedas
            $table->index(['role', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};