<?php

namespace App\Domains\Administrator\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'country' => 'nullable|string|max:100',
            'role' => 'required|in:admin,lms,seg,infra,web,data',
            'status' => 'required|in:active,inactive,banned'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio',
            'first_name.max' => 'El nombre no puede exceder los 50 caracteres',
            'last_name.required' => 'El apellido es obligatorio',
            'last_name.max' => 'El apellido no puede exceder los 50 caracteres',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe tener un formato válido',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'La confirmación de contraseña no coincide',
            'phone_number.max' => 'El número de teléfono no puede exceder los 20 caracteres',
            'address.max' => 'La dirección no puede exceder los 255 caracteres',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'gender.in' => 'El género debe ser: male, female u other',
            'country.max' => 'El país no puede exceder los 100 caracteres',
            'role.required' => 'El rol es obligatorio',
            'role.in' => 'El rol debe ser: admin, lms, seg, infra, web o data',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser: active, inactive o banned'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'phone_number' => 'número de teléfono',
            'address' => 'dirección',
            'birth_date' => 'fecha de nacimiento',
            'gender' => 'género',
            'country' => 'país',
            'role' => 'rol',
            'status' => 'estado'
        ];
    }
}
