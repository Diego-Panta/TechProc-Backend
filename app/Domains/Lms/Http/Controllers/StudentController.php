<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\StudentService;
use App\Domains\Lms\Http\Requests\CreateStudentRequest;
use App\Domains\Lms\Http\Requests\UpdateStudentRequest;
use App\Domains\Lms\Resources\StudentCollection;
use App\Domains\Lms\Resources\StudentDetailResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Display a listing of students.
     * 
     * @authenticated
     * GET /api/lms/students
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);
        
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'company_id' => $request->input('company_id'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $students = $this->studentService->getAllStudents($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => new StudentCollection($students),
        ]);
    }

    /**
     * Display the specified student.
     * 
     * @authenticated
     * GET /api/lms/students/{student_id}
     */
    public function show(int $studentId): JsonResponse
    {
        $student = $this->studentService->getStudentById($studentId);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StudentDetailResource($student),
        ]);
    }

    /**
     * Store a newly created student.
     * 
     * @authenticated
     * POST /api/lms/students
     */
    public function store(CreateStudentRequest $request): JsonResponse
    {
        $student = $this->studentService->createStudent($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Estudiante creado exitosamente',
            'data' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
            ],
        ], 201);
    }

    /**
     * Update the specified student.
     * 
     * @authenticated
     * PUT /api/lms/students/{student_id}
     */
    public function update(UpdateStudentRequest $request, int $studentId): JsonResponse
    {
        $student = $this->studentService->updateStudent($studentId, $request->validated());

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estudiante actualizado exitosamente',
        ]);
    }

    /**
     * Remove the specified student.
     * 
     * @authenticated
     * DELETE /api/lms/students/{student_id}
     */
    public function destroy(int $studentId): JsonResponse
    {
        $deleted = $this->studentService->deleteStudent($studentId);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estudiante eliminado exitosamente',
        ]);
    }
}
