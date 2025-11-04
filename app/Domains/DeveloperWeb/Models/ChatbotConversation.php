<?php

namespace Incadev\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'started_at',
        'ended_at',
        'satisfaction_rating',
        'feedback',
        'resolved',
        'handed_to_human',
        'first_message',
        'last_bot_response',
        'message_count',
        'faq_matched_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'satisfaction_rating' => 'integer',
        'resolved' => 'boolean',
        'handed_to_human' => 'boolean',
        'message_count' => 'integer',
    ];

    public function faqMatched(): BelongsTo
    {
        return $this->belongsTo(ChatbotFaq::class, 'faq_matched_id');
    }
}