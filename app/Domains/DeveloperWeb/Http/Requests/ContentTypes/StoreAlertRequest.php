<?php

namespace App\Domains\DeveloperWeb\Http\Requests\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\AlertItemType;

class StoreAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Campos REQUERIDOS para ALERT
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10|max:1000',
            'status' => 'required|string|in:' . implode(',', ContentStatus::forAlert()),
            'start_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'item_type' => 'required|string|in:' . implode(',', AlertItemType::all()),
            
            // Campos OPCIONALES para ALERT
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio',
            'content.required' => 'El contenido es obligatorio',
            'content.min' => 'El contenido debe tener al menos 10 caracteres',
            'content.max' => 'El contenido no debe exceder 1000 caracteres',
            'status.required' => 'El estado es obligatorio',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.date_format' => 'La fecha de inicio debe tener el formato Y-m-d H:i:s',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser en el pasado',
            'end_date.required' => 'La fecha de fin es obligatoria',
            'end_date.date_format' => 'La fecha de fin debe tener el formato Y-m-d H:i:s',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'item_type.required' => 'El tipo de alerta es obligatorio',
            'priority.min' => 'La prioridad debe ser al menos 1',
            'priority.max' => 'La prioridad no debe exceder 10',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Los datos proporcionados no son válidos.',
            'errors' => $validator->errors()
        ], 422));
    }
}