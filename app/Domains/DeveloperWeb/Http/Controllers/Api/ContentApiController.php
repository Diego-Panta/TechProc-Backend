<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class ContentApiController
{
    public function __construct(
        protected ContentService $contentService
    ) {}

    abstract protected function getLogIdentifier(): string;

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'search']);
            $perPage = $request->get('per_page', 15);

            $content = $this->contentService->getAll($perPage, $filters);

            Log::info("Listado de {$this->getLogIdentifier()}", ['filters' => $filters]);

            return response()->json([
                'success' => true,
                'data' => $content
            ]);

        } catch (\Exception $e) {
            Log::error("Error listing {$this->getLogIdentifier()}", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error al obtener los {$this->getLogIdentifier()}",
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $content = $this->contentService->getById($id);

            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($this->getLogIdentifier()) . ' no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $content
            ]);

        } catch (\Exception $e) {
            Log::error("Error showing {$this->getLogIdentifier()}", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error al obtener el {$this->getLogIdentifier()}",
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Método store base que puede ser sobrescrito
    public function store(Request $request): JsonResponse
    {
        try {
            $content = $this->contentService->create($request->all());

            Log::info("{$this->getLogIdentifier()} creado", [
                'id' => $content->id,
                'title' => $content->title
            ]);

            return response()->json([
                'success' => true,
                'data' => $content,
                'message' => ucfirst($this->getLogIdentifier()) . ' creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error("Error creating {$this->getLogIdentifier()}", [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error al crear el {$this->getLogIdentifier()}",
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Método update base que puede ser sobrescrito
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $success = $this->contentService->update($id, $request->all());

            if ($success) {
                Log::info("{$this->getLogIdentifier()} actualizado", ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => ucfirst($this->getLogIdentifier()) . ' actualizado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => ucfirst($this->getLogIdentifier()) . ' no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Error updating {$this->getLogIdentifier()}", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error al actualizar el {$this->getLogIdentifier()}",
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->contentService->delete($id);

            if ($success) {
                Log::info("{$this->getLogIdentifier()} eliminado", ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => ucfirst($this->getLogIdentifier()) . ' eliminado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => ucfirst($this->getLogIdentifier()) . ' no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error("Error deleting {$this->getLogIdentifier()}", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error al eliminar el {$this->getLogIdentifier()}",
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}