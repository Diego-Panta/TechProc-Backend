<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentApiController
{
    public function __construct(
        protected ContentItemRepository $contentItemRepository
    ) {}

    /**
     * Obtener todos los content_items (sin filtrar por tipo)
     * GET /api/developer-web/content
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'content_type',
                'status',
                'category',
                'search',
                'item_type',
                'sort_by',
                'sort_order'
            ]);
            $perPage = $request->get('per_page', 15);

            $content = $this->contentItemRepository->getAllPaginated($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $content
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing content', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el contenido',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener un content_item por ID (sin filtrar por tipo)
     * GET /api/developer-web/content/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $content = $this->contentItemRepository->findById($id);

            if (!$content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contenido no encontrado'
                ], 404);
            }

            // Incrementar el contador de vistas
            $this->contentItemRepository->incrementViews($content);

            return response()->json([
                'success' => true,
                'data' => $content
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing content', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el contenido'
            ], 500);
        }
    }
}
