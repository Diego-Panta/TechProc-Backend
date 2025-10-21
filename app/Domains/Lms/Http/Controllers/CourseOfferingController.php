<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Http\Requests\CreateCourseOfferingRequest;
use App\Domains\Lms\Http\Requests\UpdateCourseOfferingRequest;
use App\Domains\Lms\Services\CourseOfferingService;
use App\Domains\Lms\Resources\CourseOfferingResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CourseOfferingController extends Controller
{
    protected CourseOfferingService $courseOfferingService;

    public function __construct(CourseOfferingService $courseOfferingService)
    {
        $this->courseOfferingService = $courseOfferingService;
    }

    /**
     * Display a listing of course offerings.
     *
     * @authenticated
     * GET /api/lms/course-offerings
     */
    public function index(): JsonResponse
    {
        $courseOfferings = $this->courseOfferingService->getAllCourseOfferings();

        return response()->json([
            'success' => true,
            'data' => CourseOfferingResource::collection($courseOfferings),
        ]);
    }

    /**
     * Display the specified course offering.
     *
     * @authenticated
     * GET /api/lms/course-offerings/{course_offering_id}
     */
    public function show(int $course_offering_id): JsonResponse
    {
        $courseOffering = $this->courseOfferingService->getCourseOfferingById($course_offering_id);

        if (!$courseOffering) {
            return response()->json([
                'success' => false,
                'message' => 'Oferta de curso no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CourseOfferingResource($courseOffering),
        ]);
    }

    /**
     * Store a newly created course offering.
     *
     * @authenticated
     * POST /api/lms/course-offerings
     */
    public function store(CreateCourseOfferingRequest $request): JsonResponse
    {
        $courseOffering = $this->courseOfferingService->createCourseOffering($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Oferta de curso creada exitosamente',
            'data' => new CourseOfferingResource($courseOffering),
        ], 201);
    }

    /**
     * Update the specified course offering.
     *
     * @authenticated
     * PUT /api/lms/course-offerings/{course_offering_id}
     */
    public function update(UpdateCourseOfferingRequest $request, int $course_offering_id): JsonResponse
    {
        $updated = $this->courseOfferingService->updateCourseOffering($course_offering_id, $request->validated());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Oferta de curso no encontrada',
            ], 404);
        }

        $courseOffering = $this->courseOfferingService->getCourseOfferingById($course_offering_id);

        return response()->json([
            'success' => true,
            'message' => 'Oferta de curso actualizada exitosamente',
            'data' => new CourseOfferingResource($courseOffering),
        ]);
    }

    /**
     * Remove the specified course offering.
     *
     * @authenticated
     * DELETE /api/lms/course-offerings/{course_offering_id}
     */
    public function destroy(int $course_offering_id): JsonResponse
    {
        $deleted = $this->courseOfferingService->deleteCourseOffering($course_offering_id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Oferta de curso no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Oferta de curso eliminada exitosamente',
        ]);
    }
}
