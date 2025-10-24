<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\CourseService;
use App\Domains\Lms\Http\Requests\CreateCourseRequest;
use App\Domains\Lms\Http\Requests\UpdateCourseRequest;
use App\Domains\Lms\Resources\CourseCollection;
use App\Domains\Lms\Resources\CourseDetailResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);
        
        $filters = [
            'level' => $request->input('level'),
            'status' => $request->has('status') ? filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN) : null,
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $courses = $this->courseService->getAllCourses($filters, $perPage);

        return response()->json(['success' => true, 'data' => new CourseCollection($courses)]);
    }

    public function show(int $courseId): JsonResponse
    {
        $course = $this->courseService->getCourseById($courseId);

        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => new CourseDetailResource($course)]);
    }

    public function store(CreateCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createCourse($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Curso creado exitosamente',
            'data' => ['id' => $course->id, 'course_id' => $course->course_id],
        ], 201);
    }

    public function update(UpdateCourseRequest $request, int $courseId): JsonResponse
    {
        $course = $this->courseService->updateCourse($courseId, $request->validated());

        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Curso actualizado exitosamente']);
    }

    public function destroy(int $courseId): JsonResponse
    {
        $deleted = $this->courseService->deleteCourse($courseId);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Curso eliminado exitosamente']);
    }
}
