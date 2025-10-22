<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\DeveloperWeb\Enums\FaqCategory;

class ChatbotFaq extends Model
{
    use HasFactory;

    protected $table = 'chatbot_faqs';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos de Laravel
    public $timestamps = false;

    protected $fillable = [
        'id_faq',
        'question',
        'answer',
        'category',
        'keywords',
        'active',
        'usage_count',
        'created_date',
        'updated_date',
    ];

    protected $casts = [
        'keywords' => 'array',
        'active' => 'boolean',
        'usage_count' => 'integer',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'category' => FaqCategory::class, // Cast al enum
    ];

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class, 'faq_matched');
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeByCategory($query, ?FaqCategory $category)
    {
        if ($category) {
            return $query->where('category', $category->value);
        }
        return $query;
    }

    /**
     * Scope para FAQs activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}