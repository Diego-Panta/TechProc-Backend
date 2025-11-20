<?php

namespace App\Domains\DeveloperWeb\Http\Requests\ContentTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Domains\DeveloperWeb\Enums\ContentStatus;
use App\Domains\DeveloperWeb\Enums\AnnouncementItemType;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Campos OPCIONALES para actualización de ANNOUNCEMENT
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string|min:20',
            'image_url' => 'nullable|url|max:500',
            'status' => 'sometimes|string|in:' . implode(',', ContentStatus::forAnnouncement()),
            'start_date' => 'sometimes|date_format:Y-m-d H:i:s|after_or_equal:today',
            'end_date' => 'sometimes|date_format:Y-m-d H:i:s|after:start_date',
            'item_type' => 'nullable|string|in:' . implode(',', AnnouncementItemType::all()),
            'target_page' => 'nullable|string|max:255',
            'link_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:1|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'El título debe ser texto',
            'title.max' => 'El título no debe exceder 255 caracteres',
            'content.min' => 'El contenido debe tener al menos 20 caracteres',
            'start_date.date_format' => 'La fecha de inicio debe tener el formato Y-m-d H:i:s',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser en el pasado',
            'end_date.date_format' => 'La fecha de fin debe tener el formato Y-m-d H:i:s',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
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