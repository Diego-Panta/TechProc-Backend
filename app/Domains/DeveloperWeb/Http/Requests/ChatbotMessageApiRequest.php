<?php
// app/Domains/DeveloperWeb/Http/Requests/Api/ChatbotMessageApiRequest.php

namespace App\Domains\DeveloperWeb\Http\Requests;

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

    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje es obligatorio',
            'message.max' => 'El mensaje no puede tener más de 1000 caracteres',
            'conversation_id.required' => 'El ID de conversación es obligatorio',
            'conversation_id.exists' => 'La conversación no existe',
            'feedback.rating.between' => 'La calificación debe estar entre 1 y 5',
            'feedback.comment.max' => 'El comentario no puede tener más de 500 caracteres',
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