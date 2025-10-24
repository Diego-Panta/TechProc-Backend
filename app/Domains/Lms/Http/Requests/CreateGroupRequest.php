<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
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
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'code' => 'required|string|max:50|unique:groups,code',
            'name' => 'required|string|max:200',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:draft,approved,open,in_progress,completed,cancelled,suspended',
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
            'course_id.required' => 'El ID del curso es obligatorio',
            'course_id.exists' => 'El curso seleccionado no existe',
            'code.required' => 'El código del grupo es obligatorio',
            'code.unique' => 'El código del grupo ya existe',
            'name.required' => 'El nombre del grupo es obligatorio',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida',
            'end_date.required' => 'La fecha de fin es obligatoria',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'status.in' => 'El estado debe ser: draft, approved, open, in_progress, completed, cancelled o suspended',
        ];
    }
}
