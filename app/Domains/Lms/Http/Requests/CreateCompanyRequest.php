<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompanyRequest extends FormRequest
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
            'name' => 'required|string|max:150',
            'industry' => 'required|string|max:100',
            'contact_name' => 'required|string|max:100',
            'contact_email' => 'required|email|max:150|unique:companies,contact_email',
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
            'name.required' => 'El nombre de la empresa es obligatorio',
            'name.max' => 'El nombre de la empresa no debe exceder 150 caracteres',
            'industry.required' => 'La industria es obligatoria',
            'industry.max' => 'La industria no debe exceder 100 caracteres',
            'contact_name.required' => 'El nombre del contacto es obligatorio',
            'contact_name.max' => 'El nombre del contacto no debe exceder 100 caracteres',
            'contact_email.required' => 'El email del contacto es obligatorio',
            'contact_email.email' => 'El email del contacto debe ser una dirección de email válida',
            'contact_email.max' => 'El email del contacto no debe exceder 150 caracteres',
            'contact_email.unique' => 'El email del contacto ya está registrado',
        ];
    }
}
