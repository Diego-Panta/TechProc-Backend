<?php

namespace App\Domains\DataAnalyst\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class AttendanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Temporalmente sin autenticación
    }

    public function rules(): array
    {
        return [
            'group_id' => 'nullable|integer|exists:groups,id',
            'course_id' => 'nullable|integer|exists:courses,id',
            'student_id' => 'nullable|integer|exists:students,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'attendance_status' => 'nullable|in:YES,NO',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            
            // Filtros adicionales para estadísticas avanzadas
            'min_connected_minutes' => 'nullable|integer|min:0',
            'max_connected_minutes' => 'nullable|integer|min:0',
            'connection_quality' => 'nullable|in:EXCELLENT,GOOD,FAIR,POOR',
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.exists' => 'El grupo seleccionado no existe',
            'course_id.exists' => 'El curso seleccionado no existe',
            'student_id.exists' => 'El estudiante seleccionado no existe',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'attendance_status.in' => 'El estado de asistencia debe ser YES o NO',
            'per_page.max' => 'No se pueden mostrar más de 100 registros por página',
            'connection_quality.in' => 'La calidad de conexión no es válida',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}