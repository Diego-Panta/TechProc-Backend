<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClassMaterialRequest extends FormRequest
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
            'class_id' => 'required|integer|exists:classes,id',
            'material_url' => 'required|string|url',
            'type' => 'required|string|max:50|in:PDF,Video,Enlace,Documento,Presentación,Imagen,Audio,Otro',
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
            'class_id.required' => 'El ID de la clase es obligatorio',
            'class_id.exists' => 'La clase seleccionada no existe',
            'material_url.required' => 'La URL del material es obligatoria',
            'material_url.url' => 'La URL del material debe ser válida',
            'type.required' => 'El tipo de material es obligatorio',
            'type.max' => 'El tipo de material no puede exceder 50 caracteres',
            'type.in' => 'El tipo debe ser: PDF, Video, Enlace, Documento, Presentación, Imagen, Audio u Otro',
        ];
    }
}
