<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Http\Requests\CreateAcademicPeriodRequest;
use App\Domains\Lms\Http\Requests\UpdateAcademicPeriodRequest;
use App\Domains\Lms\Services\AcademicPeriodService;
use App\Domains\Lms\Resources\AcademicPeriodResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AcademicPeriodController extends Controller
{
    protected AcademicPeriodService $academicPeriodService;

    public function __construct(AcademicPeriodService $academicPeriodService)
    {
        $this->academicPeriodService = $academicPeriodService;
    }

    /**
     * Display a listing of academic periods.
     *
     * @authenticated
     * GET /api/lms/academic-periods
     */
    public function index(): JsonResponse
    {
        $academicPeriods = $this->academicPeriodService->getAllAcademicPeriods();

        return response()->json([
            'success' => true,
            'data' => AcademicPeriodResource::collection($academicPeriods),
        ]);
    }

    /**
     * Display the specified academic period.
     *
     * @authenticated
     * GET /api/lms/academic-periods/{academic_period_id}
     */
    public function show(int $academic_period_id): JsonResponse
    {
        $academicPeriod = $this->academicPeriodService->getAcademicPeriodById($academic_period_id);

        if (!$academicPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo académico no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AcademicPeriodResource($academicPeriod),
        ]);
    }

    /**
     * Store a newly created academic period.
     *
     * @authenticated
     * POST /api/lms/academic-periods
     */
    public function store(CreateAcademicPeriodRequest $request): JsonResponse
    {
        $academicPeriod = $this->academicPeriodService->createAcademicPeriod($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Periodo académico creado exitosamente',
            'data' => new AcademicPeriodResource($academicPeriod),
        ], 201);
    }

    /**
     * Update the specified academic period.
     *
     * @authenticated
     * PUT /api/lms/academic-periods/{academic_period_id}
     */
    public function update(UpdateAcademicPeriodRequest $request, int $academic_period_id): JsonResponse
    {
        $updated = $this->academicPeriodService->updateAcademicPeriod($academic_period_id, $request->validated());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo académico no encontrado',
            ], 404);
        }

        $academicPeriod = $this->academicPeriodService->getAcademicPeriodById($academic_period_id);

        return response()->json([
            'success' => true,
            'message' => 'Periodo académico actualizado exitosamente',
            'data' => new AcademicPeriodResource($academicPeriod),
        ]);
    }

    /**
     * Remove the specified academic period.
     *
     * @authenticated
     * DELETE /api/lms/academic-periods/{academic_period_id}
     */
    public function destroy(int $academic_period_id): JsonResponse
    {
        $deleted = $this->academicPeriodService->deleteAcademicPeriod($academic_period_id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Periodo académico no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Periodo académico eliminado exitosamente',
        ]);
    }
}
