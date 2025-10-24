<?php

namespace App\Domains\Lms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClassRequest extends FormRequest
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
            'group_id' => 'required|integer|exists:groups,id',
            'class_name' => 'required|string|max:100',
            'meeting_url' => 'nullable|string|max:500|url',
            'description' => 'nullable|string',
            'class_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'class_status' => 'nullable|in:SCHEDULED,IN_PROGRESS,FINISHED,CANCELLED',
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
            'group_id.required' => 'El ID del grupo es obligatorio',
            'group_id.exists' => 'El grupo seleccionado no existe',
            'class_name.required' => 'El nombre de la clase es obligatorio',
            'class_name.max' => 'El nombre de la clase no puede exceder 100 caracteres',
            'meeting_url.url' => 'La URL de la reunión debe ser válida',
            'class_date.required' => 'La fecha de la clase es obligatoria',
            'class_date.date' => 'La fecha de la clase debe ser una fecha válida',
            'class_date.after_or_equal' => 'La fecha de la clase no puede ser anterior a hoy',
            'start_time.required' => 'La hora de inicio es obligatoria',
            'start_time.date_format' => 'La hora de inicio debe tener el formato HH:mm',
            'end_time.required' => 'La hora de fin es obligatoria',
            'end_time.date_format' => 'La hora de fin debe tener el formato HH:mm',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'class_status.in' => 'El estado debe ser: SCHEDULED, IN_PROGRESS, FINISHED o CANCELLED',
        ];
    }
}
