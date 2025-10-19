<?php

namespace App\Domains\Lms\Http\Controllers;

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
}
