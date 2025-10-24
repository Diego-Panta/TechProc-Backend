<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\ClassMaterialService;
use App\Domains\Lms\Http\Requests\CreateClassMaterialRequest;
use App\Domains\Lms\Http\Requests\UpdateClassMaterialRequest;
use App\Domains\Lms\Resources\ClassMaterialCollection;
use App\Domains\Lms\Resources\ClassMaterialResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassMaterialController extends Controller
{
    protected ClassMaterialService $materialService;

    public function __construct(ClassMaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    /**
     * Display a listing of class materials
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);

        $filters = [
            'class_id' => $request->input('class_id'),
            'type' => $request->input('type'),
            'search' => $request->input('search'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $materials = $this->materialService->getAllMaterials($filters, $perPage);

        return response()->json(['success' => true, 'data' => new ClassMaterialCollection($materials)]);
    }

    /**
     * Display the specified class material
     */
    public function show(int $materialId): JsonResponse
    {
        $material = $this->materialService->getMaterialById($materialId);

        if (!$material) {
            return response()->json(['success' => false, 'message' => 'Material no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => new ClassMaterialResource($material)]);
    }

    /**
     * Store a newly created class material
     */
    public function store(CreateClassMaterialRequest $request): JsonResponse
    {
        $material = $this->materialService->createMaterial($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Material creado exitosamente',
            'data' => ['id' => $material->id],
        ], 201);
    }

    /**
     * Update the specified class material
     */
    public function update(UpdateClassMaterialRequest $request, int $materialId): JsonResponse
    {
        $material = $this->materialService->updateMaterial($materialId, $request->validated());

        if (!$material) {
            return response()->json(['success' => false, 'message' => 'Material no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Material actualizado exitosamente']);
    }

    /**
     * Remove the specified class material
     */
    public function destroy(int $materialId): JsonResponse
    {
        $deleted = $this->materialService->deleteMaterial($materialId);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Material no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Material eliminado exitosamente']);
    }
}
