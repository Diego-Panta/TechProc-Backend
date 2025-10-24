<?php
// app/Domains/DeveloperWeb/Models/ChatbotMessage.php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    use HasFactory;

    protected $table = 'chatbot_messages';
    protected $primaryKey = 'id';

    // Deshabilitar timestamps automáticos
    public $timestamps = false;

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
        'id_message' => 'integer', // Asegurar que sea integer
        'conversation_id' => 'integer',
        'faq_matched' => 'integer',
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