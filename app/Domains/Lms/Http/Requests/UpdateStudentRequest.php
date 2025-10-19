<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
        $studentId = $this->route('student_id');
        
        return [
            'company_id' => 'nullable|integer|exists:companies,id',
            'document_number' => 'sometimes|string|max:20|unique:students,document_number,' . $studentId,
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:students,email,' . $studentId,
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|in:active,inactive',
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
            'company_id.exists' => 'La empresa especificada no existe',
            'document_number.unique' => 'Este número de documento ya está registrado',
            'email.email' => 'El formato del correo electrónico no es válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'status.in' => 'El estado debe ser: active o inactive',
        ];
    }
}
