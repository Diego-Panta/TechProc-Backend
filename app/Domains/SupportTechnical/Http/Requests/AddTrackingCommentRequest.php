<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTrackingCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => 'required|string',
            'action_type' => 'required|in:assignment,update,resolution,closing,escalation',
            // user_id es opcional - se obtendrá del usuario autenticado
            'user_id' => 'sometimes|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'El comentario es requerido',
            'action_type.required' => 'El tipo de acción es requerido',
            'action_type.in' => 'El tipo de acción debe ser: assignment, update, resolution, closing o escalation',
            'user_id.integer' => 'El usuario debe ser un número entero',
            'user_id.exists' => 'El usuario no existe',
        ];
    }
}
