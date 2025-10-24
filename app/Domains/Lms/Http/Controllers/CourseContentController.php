<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\CourseContentService;
use App\Domains\Lms\Http\Requests\CreateCourseContentRequest;
use App\Domains\Lms\Http\Requests\UpdateCourseContentRequest;
use App\Domains\Lms\Resources\CourseContentCollection;
use App\Domains\Lms\Resources\CourseContentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseContentController extends Controller
{
    protected CourseContentService $contentService;

    public function __construct(CourseContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);

        $filters = [
            'course_id' => $request->input('course_id'),
            'type' => $request->input('type'),
            'session' => $request->input('session'),
            'search' => $request->input('search'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $contents = $this->contentService->getAllContents($filters, $perPage);

        return response()->json(['success' => true, 'data' => new CourseContentCollection($contents)]);
    }

    public function show(int $contentId): JsonResponse
    {
        $content = $this->contentService->getContentById($contentId);

        if (!$content) {
            return response()->json(['success' => false, 'message' => 'Contenido no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => new CourseContentResource($content)]);
    }

    public function store(CreateCourseContentRequest $request): JsonResponse
    {
        $content = $this->contentService->createContent($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contenido creado exitosamente',
            'data' => ['id' => $content->id],
        ], 201);
    }

    public function update(UpdateCourseContentRequest $request, int $contentId): JsonResponse
    {
        $content = $this->contentService->getContentById($contentId);

        if (!$content) {
            return response()->json(['success' => false, 'message' => 'Contenido no encontrado'], 404);
        }

        $this->contentService->updateContent($contentId, $request->validated());

        return response()->json(['success' => true, 'message' => 'Contenido actualizado exitosamente']);
    }

    public function destroy(int $contentId): JsonResponse
    {
        $deleted = $this->contentService->deleteContent($contentId);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Contenido no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Contenido eliminado exitosamente']);
    }
}
