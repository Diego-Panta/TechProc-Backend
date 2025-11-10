<?php

namespace App\Domains\SupportInfrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\SupportInfrastructure\Services\LicenseAssignmentService;
use Illuminate\Http\JsonResponse;

class LicenseAssignmentController extends Controller
{
    protected $licenseAssignmentService;

    public function __construct(LicenseAssignmentService $licenseAssignmentService)
    {
        $this->licenseAssignmentService = $licenseAssignmentService;
    }

    /**
     * Mostrar todas las asignaciones de licencias.
     */
    public function index(): JsonResponse
    {
        try {
            $assignments = $this->licenseAssignmentService->getAll();
            return response()->json(['success' => true, 'data' => $assignments]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar asignaciones', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar una asignación específica.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $assignment = $this->licenseAssignmentService->getById($id);
            return response()->json(['success' => true, 'data' => $assignment]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se encontró la asignación', 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Crear una nueva asignación de licencia.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_id' => 'required|exists:licenses,id',
            'asset_id' => 'required|exists:tech_assets,id',
            'assigned_date' => 'required|date',
            'status' => 'required|string|max:100'
        ]);

        try {
            $assignment = $this->licenseAssignmentService->create($validated);
            return response()->json(['success' => true, 'data' => $assignment], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear asignación', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar una asignación existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'license_id' => 'sometimes|exists:licenses,id',
            'asset_id' => 'sometimes|exists:tech_assets,id',
            'assigned_date' => 'sometimes|date',
            'status' => 'sometimes|string|max:100'
        ]);

        try {
            $assignment = $this->licenseAssignmentService->update($id, $validated);
            return response()->json(['success' => true, 'data' => $assignment]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar asignación', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una asignación.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->licenseAssignmentService->delete($id);
            return response()->json(['success' => true, 'message' => 'Asignación eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar asignación', 'error' => $e->getMessage()], 500);
        }
    }
}
