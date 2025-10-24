<?php

namespace App\Domains\Lms\Services;

use App\Domains\Lms\Models\Instructor;
use App\Domains\Lms\Repositories\InstructorRepositoryInterface;
use App\Domains\Administrator\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InstructorService
{
    protected InstructorRepositoryInterface $repository;

    public function __construct(InstructorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllInstructors(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function getInstructorById(int $instructorId): ?Instructor
    {
        return $this->repository->findById($instructorId);
    }

    public function createInstructor(array $data): Instructor
    {
        return DB::transaction(function () use ($data) {
            // Crear el usuario primero con los datos básicos proporcionados
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'full_name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone_number' => $data['phone_number'] ?? null,
                'document' => $data['document_number'] ?? null,
                'dni' => $data['document_number'] ?? null,
                'role' => ['instructor'], // Rol de instructor
                'status' => $data['status'] ?? 'active',
                'timezone' => 'America/Lima',
            ]);

            // Preparar los datos del instructor sin los campos de usuario
            $instructorData = [
                'user_id' => $user->id,
                'bio' => $data['bio'] ?? null,
                'expertise_area' => $data['expertise_area'],
                'status' => $data['status'] ?? 'active',
            ];

            // Crear el instructor
            $instructor = $this->repository->create($instructorData);

            if (!$instructor->instructor_id) {
                $instructor->instructor_id = $instructor->id;
                $instructor->save();
            }

            return $instructor->fresh(['user']);
        });
    }

    public function updateInstructor(int $instructorId, array $data): Instructor
    {
        return $this->repository->update($instructorId, $data);
    }
}
