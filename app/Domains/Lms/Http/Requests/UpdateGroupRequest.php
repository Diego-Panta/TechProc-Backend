<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Implementar lógica de autorización cuando el módulo de autenticación esté disponible
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $groupId = $this->route('id');

        return [
            'course_id' => 'sometimes|integer|exists:courses,id',
            'code' => "sometimes|string|max:50|unique:groups,code,{$groupId}",
            'name' => 'sometimes|string|max:200',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:draft,approved,open,in_progress,completed,cancelled,suspended',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.exists' => 'El curso seleccionado no existe',
            'code.unique' => 'El código del grupo ya existe',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'status.in' => 'El estado debe ser: draft, approved, open, in_progress, completed, cancelled o suspended',
        ];
    }
}
