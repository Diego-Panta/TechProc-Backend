<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    use HasFactory;

    protected $table = 'chatbot_messages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_message',
        'conversation_id',
        'sender',
        'message',
        'timestamp',
        'faq_matched',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }

    public function faq()
    {
        return $this->belongsTo(ChatbotFaq::class, 'faq_matched');
    }
}