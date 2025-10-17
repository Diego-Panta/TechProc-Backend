<?php
// app/Domains/DeveloperWeb/Http/Requests/Api/ChatbotMessageApiRequest.php

namespace App\Domains\DeveloperWeb\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ChatbotMessageApiRequest extends FormRequest
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
            'feedback' => 'sometimes|array',
            'feedback.rating' => 'sometimes|integer|between:1,5',
            'feedback.comment' => 'sometimes|string|max:500',
            'feedback.resolved' => 'sometimes|boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}