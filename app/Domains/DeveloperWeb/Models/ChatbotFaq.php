<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    use HasFactory;

    protected $table = 'chatbot_faqs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_faq',
        'question',
        'answer',
        'category',
        'keywords',
        'active',
        'usage_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'active' => 'boolean',
        'usage_count' => 'integer',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class, 'faq_matched');
    }
}