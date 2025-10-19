<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Http\Requests\Api\StoreNewsApiRequest;
use App\Domains\DeveloperWeb\Http\Requests\Api\UpdateNewsApiRequest;
use App\Domains\DeveloperWeb\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsApiController
{
    public function __construct(
        private NewsService $newsService
    ) {}

    /**
     * Listar noticias (PROTEGIDO)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado desde el middleware
            $user = $request->user();
            
            $filters = [
                'status' => $request->get('status'),
                'category' => $request->get('category'),
                'search' => $request->get('search'),
            ];

            $perPage = $request->get('per_page', 15);

            $news = $this->newsService->getAllNews($perPage, $filters);

            // Log de acceso
            Log::info('Usuario accedió a listado de noticias', [
                'user_id' => $user->id,
                'email' => $user->email,
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing news', [
                'user_id' => $request->user()->id ?? 'unknown',
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las noticias',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener noticia específica (PROTEGIDO)
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $news = $this->newsService->getNewsById($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            Log::info('Usuario accedió a noticia específica', [
                'user_id' => $user->id,
                'news_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing news', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Crear nueva noticia (PROTEGIDO)
     */
    public function store(StoreNewsApiRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Pasar el ID del usuario autenticado al servicio
            $news = $this->newsService->createNews(
                $request->validated(), 
                $user->id
            );

            // Log de creación
            Log::info('Usuario creó nueva noticia', [
                'user_id' => $user->id,
                'news_id' => $news->id,
                'title' => $news->title
            ]);

            return response()->json([
                'success' => true,
                'data' => $news,
                'message' => 'Noticia creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating news', [
                'user_id' => $request->user()->id ?? 'unknown',
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Actualizar noticia (PROTEGIDO)
     */
    public function update(UpdateNewsApiRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $success = $this->newsService->updateNews($id, $request->validated());

            if ($success) {
                Log::info('Usuario actualizó noticia', [
                    'user_id' => $user->id,
                    'news_id' => $id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Noticia actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la noticia'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error updating news', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Eliminar noticia (PROTEGIDO)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $success = $this->newsService->deleteNews($id);

            if ($success) {
                Log::info('Usuario eliminó noticia', [
                    'user_id' => $user->id,
                    'news_id' => $id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Noticia eliminada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la noticia'
            ], 404);

        } catch (\Exception $e) {
            Log::error('API Error deleting news', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener estadísticas (PROTEGIDO)
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $statusCounts = $this->newsService->getStatusCounts();
            $categoryCounts = $this->newsService->getCategoryCounts();
            $publishedNews = $this->newsService->getPublishedNews();
            
            $total = array_sum($statusCounts);
            $totalViews = \App\Domains\DeveloperWeb\Models\News::sum('views');

            Log::info('Usuario accedió a estadísticas de noticias', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'draft' => $statusCounts['draft'] ?? 0,
                    'published' => $statusCounts['published'] ?? 0,
                    'archived' => $statusCounts['archived'] ?? 0,
                    'total_views' => $totalViews,
                    'categories_count' => count($categoryCounts),
                    'published_count' => $publishedNews->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting news stats', [
                'user_id' => $request->user()->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtener noticias relacionadas (PROTEGIDO)
     */
    public function getRelatedNews(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $limit = $request->get('limit', 3);
            $relatedNews = $this->newsService->getRelatedNews($id);

            Log::info('Usuario accedió a noticias relacionadas', [
                'user_id' => $user->id,
                'news_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'data' => array_slice($relatedNews, 0, $limit)
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting related news', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener noticias relacionadas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Resetear vistas (PROTEGIDO)
     */
    public function resetViews(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $news = $this->newsService->getNewsById($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            $news->update(['views' => 0]);

            Log::info('Usuario reseteó vistas de noticia', [
                'user_id' => $user->id,
                'news_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vistas reseteadas a 0',
                'views' => 0
            ]);

        } catch (\Exception $e) {
            Log::error('API Error resetting news views', [
                'user_id' => $request->user()->id ?? 'unknown',
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al resetear las vistas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Los métodos públicos permanecen igual (sin cambios)
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $filters = [
                'category' => $request->get('category'),
                'search' => $request->get('search'),
                'limit' => $request->get('limit', 10),
            ];

            $news = $this->newsService->getNewsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'news' => $news,
                    'pagination' => [
                        'current_page' => 1,
                        'total_pages' => ceil(count($news) / $filters['limit']),
                        'total_records' => count($news),
                        'per_page' => $filters['limit']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing public news', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las noticias públicas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function publicShow(int $id): JsonResponse
    {
        try {
            $news = $this->newsService->getNewsById($id);

            if (!$news || $news->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            // Incrementar vistas
            $this->newsService->incrementViews($id);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing public news', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function publicShowBySlug(string $slug): JsonResponse
    {
        try {
            $news = $this->newsService->getNewsBySlug($slug);

            if (!$news || $news->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            // Incrementar vistas
            $this->newsService->incrementViews($news->id);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing public news by slug', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getCategories(): JsonResponse
    {
        try {
            $categoryCounts = $this->newsService->getCategoryCounts();
            $categories = array_keys($categoryCounts);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting news categories', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}