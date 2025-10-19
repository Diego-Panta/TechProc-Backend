<?php
// app/Domains/DeveloperWeb/Http/Requests/ChatbotMessageRequest.php

namespace App\Domains\DeveloperWeb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
            'conversation_id' => 'required|integer|exists:chatbot_conversations,id',
        ];
    }
}