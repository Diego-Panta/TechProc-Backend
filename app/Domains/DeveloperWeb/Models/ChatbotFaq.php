<?php

namespace Incadev\Core\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    protected $fillable = [
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
    ];

    public function conversationsMatched(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class, 'faq_matched_id');
    }
}