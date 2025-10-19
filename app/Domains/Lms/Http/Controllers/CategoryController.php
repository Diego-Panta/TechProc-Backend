<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Http\Requests\CreateCategoryRequest;
use App\Domains\Lms\Http\Requests\UpdateCategoryRequest;
use App\Domains\Lms\Services\CategoryService;
use App\Domains\Lms\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories.
     *
     * @authenticated
     * GET /api/lms/categories
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Display the specified category.
     *
     * @authenticated
     * GET /api/lms/categories/{category_id}
     */
    public function show(int $category_id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($category_id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Store a newly created category.
     *
     * @authenticated
     * POST /api/lms/categories
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Update the specified category.
     *
     * @authenticated
     * PUT /api/lms/categories/{category_id}
     */
    public function update(UpdateCategoryRequest $request, int $category_id): JsonResponse
    {
        $updated = $this->categoryService->updateCategory($category_id, $request->validated());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada',
            ], 404);
        }

        $category = $this->categoryService->getCategoryById($category_id);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Remove the specified category.
     *
     * @authenticated
     * DELETE /api/lms/categories/{category_id}
     */
    public function destroy(int $category_id): JsonResponse
    {
        $deleted = $this->categoryService->deleteCategory($category_id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente',
        ]);
    }
}
