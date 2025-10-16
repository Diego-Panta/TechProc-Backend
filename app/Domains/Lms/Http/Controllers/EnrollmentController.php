<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\EnrollmentService;
use App\Domains\Lms\Http\Requests\CreateEnrollmentRequest;
use App\Domains\Lms\Resources\EnrollmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    protected EnrollmentService $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display a listing of enrollments.
     * 
     * @authenticated
     * GET /api/lms/enrollments
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'student_id' => $request->input('student_id'),
            'academic_period_id' => $request->input('academic_period_id'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $enrollments = $this->enrollmentService->getAllEnrollments($filters);

        return response()->json([
            'success' => true,
            'data' => EnrollmentResource::collection($enrollments),
        ]);
    }

    /**
     * Store a newly created enrollment.
     * 
     * @authenticated
     * POST /api/lms/enrollments
     */
    public function store(CreateEnrollmentRequest $request): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->createEnrollment($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Matrícula creada exitosamente',
                'data' => [
                    'enrollment_id' => $enrollment->enrollment_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la matrícula',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
