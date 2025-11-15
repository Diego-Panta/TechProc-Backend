<?php

namespace App\Domains\SupportTechnical\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:5'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip'
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
            'content.required' => 'El contenido de la respuesta es requerido',
            'content.min' => 'El contenido debe tener al menos 5 caracteres',
            'attachments.max' => 'No puedes subir más de 5 archivos',
            'attachments.*.file' => 'Cada adjunto debe ser un archivo válido',
            'attachments.*.max' => 'Cada archivo no puede exceder los 10MB',
            'attachments.*.mimes' => 'Los archivos permitidos son: jpg, jpeg, png, pdf, doc, docx, txt, zip',
        ];
    }
}
