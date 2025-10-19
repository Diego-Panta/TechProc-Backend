<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
        $companyId = $this->route('company_id');

        return [
            'name' => 'sometimes|string|max:150',
            'industry' => 'sometimes|string|max:100',
            'contact_name' => 'sometimes|string|max:100',
            'contact_email' => [
                'sometimes',
                'email',
                'max:150',
                Rule::unique('companies', 'contact_email')->ignore($companyId),
            ],
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
            'name.string' => 'El nombre de la empresa debe ser texto',
            'name.max' => 'El nombre de la empresa no debe exceder 150 caracteres',
            'industry.string' => 'La industria debe ser texto',
            'industry.max' => 'La industria no debe exceder 100 caracteres',
            'contact_name.string' => 'El nombre del contacto debe ser texto',
            'contact_name.max' => 'El nombre del contacto no debe exceder 100 caracteres',
            'contact_email.email' => 'El email del contacto debe ser una dirección de email válida',
            'contact_email.max' => 'El email del contacto no debe exceder 150 caracteres',
            'contact_email.unique' => 'El email del contacto ya está registrado',
        ];
    }
}
