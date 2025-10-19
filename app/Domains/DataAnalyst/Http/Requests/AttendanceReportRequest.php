<?php

namespace App\Domains\DataAnalyst\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'group_id' => 'nullable|integer|exists:groups,id',
            'course_id' => 'nullable|integer|exists:courses,id',
            'student_id' => 'nullable|integer|exists:students,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'attendance_status' => 'nullable|in:YES,NO',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'group_id.exists' => 'El grupo seleccionado no existe',
            'course_id.exists' => 'El curso seleccionado no existe',
            'student_id.exists' => 'El estudiante seleccionado no existe',
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial',
            'attendance_status.in' => 'El estado de asistencia debe ser YES o NO',
        ];
    }
}