<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\InstructorService;
use App\Domains\Lms\Http\Requests\CreateInstructorRequest;
use App\Domains\Lms\Http\Requests\UpdateInstructorRequest;
use App\Domains\Lms\Resources\InstructorCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    protected InstructorService $instructorService;

    public function __construct(InstructorService $instructorService)
    {
        $this->instructorService = $instructorService;
    }

    /**
     * Display a listing of instructors.
     * 
     * @authenticated
     * GET /api/lms/instructors
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);
        
        $filters = [
            'status' => $request->input('status'),
            'expertise_area' => $request->input('expertise_area'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $instructors = $this->instructorService->getAllInstructors($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => new InstructorCollection($instructors),
        ]);
    }

    /**
     * Store a newly created instructor.
     *
     * @authenticated
     * POST /api/lms/instructors
     *
     * Crea automáticamente un usuario con los datos proporcionados.
     * El usuario se crea con rol "instructor" y las credenciales permiten hacer login.
     */
    public function store(CreateInstructorRequest $request): JsonResponse
    {
        $instructor = $this->instructorService->createInstructor($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Instructor y usuario creados exitosamente',
            'data' => [
                'id' => $instructor->id,
                'instructor_id' => $instructor->instructor_id,
                'user_id' => $instructor->user_id,
                'email' => $instructor->user->email ?? null,
                'full_name' => $instructor->user->full_name ?? null,
                'status' => $instructor->status,
            ],
        ], 201);
    }

    /**
     * Update the specified instructor.
     * 
     * @authenticated
     * PUT /api/lms/instructors/{instructor_id}
     */
    public function update(UpdateInstructorRequest $request, int $instructorId): JsonResponse
    {
        $instructor = $this->instructorService->updateInstructor($instructorId, $request->validated());

        if (!$instructor) {
            return response()->json([
                'success' => false,
                'message' => 'Instructor no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Instructor actualizado exitosamente',
        ]);
    }
}
