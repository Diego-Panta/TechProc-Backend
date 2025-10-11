<?php

namespace App\Domains\DeveloperWeb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $table = 'chatbot_conversations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_conversation',
        'started_date',
        'ended_date',
        'satisfaction_rating',
        'feedback',
        'resolved',
        'handed_to_human',
    ];

    protected $casts = [
        'started_date' => 'datetime',
        'ended_date' => 'datetime',
        'satisfaction_rating' => 'integer',
        'resolved' => 'boolean',
        'handed_to_human' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class, 'conversation_id');
    }
}