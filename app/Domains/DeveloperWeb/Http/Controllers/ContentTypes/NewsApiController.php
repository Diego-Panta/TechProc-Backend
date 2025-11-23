<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentTypes\NewsService;
use App\Domains\DeveloperWeb\Http\Requests\ContentTypes\StoreNewsRequest;
use App\Domains\DeveloperWeb\Http\Requests\ContentTypes\UpdateNewsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsApiController
{
    public function __construct(
        protected NewsService $newsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'category', 'search']);
            $perPage = $request->get('per_page', 15);

            $news = $this->newsService->getAll($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing news', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las noticias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $news = $this->newsService->getById($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            // Incrementar el contador de vistas
            $this->newsService->incrementViews($id);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing news', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia'
            ], 500);
        }
    }

    public function store(StoreNewsRequest $request): JsonResponse
    {
        try {
            $news = $this->newsService->create($request->validated());

            Log::info('Noticia creada', ['id' => $news->id, 'title' => $news->title]);

            return response()->json([
                'success' => true,
                'data' => $news,
                'message' => 'Noticia creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating news', ['data' => $request->all(), 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la noticia'
            ], 500);
        }
    }

    public function update(UpdateNewsRequest $request, int $id): JsonResponse
    {
        try {
            $success = $this->newsService->update($id, $request->validated());

            if ($success) {
                Log::info('Noticia actualizada', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Noticia actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Noticia no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error updating news', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la noticia'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->newsService->delete($id);

            if ($success) {
                Log::info('Noticia eliminada', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Noticia eliminada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Noticia no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting news', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la noticia'
            ], 500);
        }
    }

    // MÉTODOS ESPECÍFICOS SOLICITADOS
    
    public function getPublished(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $news = $this->newsService->getPublishedNews($perPage);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting published news', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener noticias publicadas'
            ], 500);
        }
    }

    public function resetViews(int $id): JsonResponse
    {
        try {
            $success = $this->newsService->resetViews($id);

            if ($success) {
                Log::info('Vistas de noticia reseteadas', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Vistas reseteadas exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Noticia no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error resetting news views', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al resetear vistas'
            ], 500);
        }
    }

    /**
     * Obtener categorías disponibles (ahora desde el enum)
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->newsService->getCategories();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting news categories', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de noticias
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->newsService->getStats();

            Log::info('Estadísticas de noticias consultadas');

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de noticias', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas de noticias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}