<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            
            // Tipo de contenido - string simple
            $table->string('content_type'); // news, announcement, alert
            
            // Campos comunes básicos
            $table->string('title')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->text('content');
            $table->text('summary')->nullable();
            
            // Campos de imágenes
            $table->string('image_url')->nullable();
            
            // Campos de estado y visualización - strings simples
            $table->string('status')->default('draft'); // Usa tus enums nativos
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedInteger('priority')->default(1);
            
            // Campos de fechas
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('published_date')->nullable();
            $table->timestamps();
            
            // Campos específicos por tipo
            $table->string('category')->nullable();
            $table->string('item_type')->nullable(); // tipo específico dentro de cada categoría
            $table->string('target_page')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_text')->nullable();
            $table->string('button_text')->nullable();
            
            // Campos para SEO
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            
            // Metadata flexible
            $table->json('metadata')->nullable();
            
            // Índices
            $table->index('content_type');
            $table->index(['status', 'content_type']);
            $table->index(['start_date', 'end_date']);
            $table->index('published_date');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};