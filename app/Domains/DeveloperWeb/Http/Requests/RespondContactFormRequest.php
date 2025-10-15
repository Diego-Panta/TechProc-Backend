<?php

namespace App\Domains\DeveloperWeb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RespondContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        //return auth()->check(); // Solo usuarios autenticados pueden responder
        return true;
    }

    public function rules(): array
    {
        return [
            'response' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'response.required' => 'La respuesta es obligatoria',
            'response.min' => 'La respuesta debe tener al menos 10 caracteres',
        ];
    }
}