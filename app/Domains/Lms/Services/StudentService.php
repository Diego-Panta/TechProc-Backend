<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Student;
use App\Domains\Lms\Repositories\StudentRepositoryInterface;
use App\Domains\Administrator\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentService
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllStudents(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getStudentById(int $studentId): ?Student
    {
        return $this->repository->findById($studentId);
    }

    public function createStudent(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Crear el usuario primero con los datos del estudiante
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'full_name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone_number' => $data['phone'] ?? null,
                'document' => $data['document_number'],
                'dni' => $data['document_number'],
                'role' => ['student'], // Rol de estudiante
                'status' => $data['status'] ?? 'active',
                'timezone' => 'America/Lima',
            ]);

            // Preparar los datos del estudiante sin el password
            $studentData = $data;
            unset($studentData['password']); // Eliminar password ya que no se guarda en la tabla students
            $studentData['user_id'] = $user->id; // Asignar el user_id recién creado
            $studentData['status'] = $data['status'] ?? 'active';

            // Crear el estudiante
            $student = $this->repository->create($studentData);

            if (!$student->student_id) {
                $student->student_id = $student->id;
                $student->save();
            }

            return $student->fresh(['company', 'user']);
        });
    }

    public function updateStudent(int $studentId, array $data): Student
    {
        return $this->repository->update($studentId, $data);
    }

    public function deleteStudent(int $studentId): bool
    {
        return $this->repository->delete($studentId);
    }
}
