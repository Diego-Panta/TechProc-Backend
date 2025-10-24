<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\ClassService;
use App\Domains\Lms\Http\Requests\CreateClassRequest;
use App\Domains\Lms\Http\Requests\UpdateClassRequest;
use App\Domains\Lms\Resources\ClassCollection;
use App\Domains\Lms\Resources\ClassResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    protected ClassService $classService;

    public function __construct(ClassService $classService)
    {
        $this->classService = $classService;
    }

    /**
     * Display a listing of classes
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);

        $filters = [
            'group_id' => $request->input('group_id'),
            'class_status' => $request->input('class_status'),
            'search' => $request->input('search'),
            'class_date_from' => $request->input('class_date_from'),
            'class_date_to' => $request->input('class_date_to'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $classes = $this->classService->getAllClasses($filters, $perPage);

        return response()->json(['success' => true, 'data' => new ClassCollection($classes)]);
    }

    /**
     * Display the specified class
     */
    public function show(int $classId): JsonResponse
    {
        $class = $this->classService->getClassById($classId);

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => new ClassResource($class)]);
    }

    /**
     * Store a newly created class
     */
    public function store(CreateClassRequest $request): JsonResponse
    {
        $class = $this->classService->createClass($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Clase creada exitosamente',
            'data' => ['id' => $class->id],
        ], 201);
    }

    /**
     * Update the specified class
     */
    public function update(UpdateClassRequest $request, int $classId): JsonResponse
    {
        $class = $this->classService->updateClass($classId, $request->validated());

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Clase actualizada exitosamente']);
    }

    /**
     * Remove the specified class
     */
    public function destroy(int $classId): JsonResponse
    {
        $deleted = $this->classService->deleteClass($classId);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Clase eliminada exitosamente']);
    }
}
