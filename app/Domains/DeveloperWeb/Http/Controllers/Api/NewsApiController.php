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
     * @OA\Get(
     *     path="/api/developer-web/news",
     *     summary="Listar noticias (admin)",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "published", "archived"})
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por título o resumen",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página para paginación",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de noticias",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/News")
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'category' => $request->get('category'),
                'search' => $request->get('search'),
            ];

            $perPage = $request->get('per_page', 15);

            $news = $this->newsService->getAllNews($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing news', [
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
     * @OA\Get(
     *     path="/api/developer-web/news/public",
     *     summary="Listar noticias públicas activas",
     *     tags={"News"},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar por título o resumen",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Límite de resultados",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página para paginación",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de noticias públicas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="news", type="array",
     *                     @OA\Items(ref="#/components/schemas/News")
     *                 ),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="total_pages", type="integer", example=5),
     *                     @OA\Property(property="total_records", type="integer", example=92),
     *                     @OA\Property(property="per_page", type="integer", example=20)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/developer-web/news/{id}",
     *     summary="Obtener detalles de una noticia",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la noticia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la noticia",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/News")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Noticia no encontrada"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $news = $this->newsService->getNewsById($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $news
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing news', [
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
     * @OA\Get(
     *     path="/api/developer-web/news/public/{id}",
     *     summary="Obtener detalles de una noticia pública",
     *     tags={"News"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la noticia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la noticia pública",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/News")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Noticia no encontrada"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/developer-web/news/public/slug/{slug}",
     *     summary="Obtener detalles de una noticia pública por slug",
     *     tags={"News"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Slug de la noticia",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la noticia pública",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/News")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Noticia no encontrada"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/developer-web/news",
     *     summary="Crear una nueva noticia",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "summary", "content", "category", "status"},
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="summary", type="string", minLength=10, maxLength=500),
     *             @OA\Property(property="content", type="string", minLength=50),
     *             @OA\Property(property="featured_image", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="author_id", type="integer", nullable=true),
     *             @OA\Property(property="category", type="string", maxLength=100),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string", maxLength=50), nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}),
     *             @OA\Property(property="published_date", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="seo_title", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="seo_description", type="string", maxLength=500, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Noticia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/News"),
     *             @OA\Property(property="message", type="string", example="Noticia creada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(StoreNewsApiRequest $request): JsonResponse
    {
        try {
            $news = $this->newsService->createNews($request->validated());

            return response()->json([
                'success' => true,
                'data' => $news,
                'message' => 'Noticia creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating news', [
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
     * @OA\Put(
     *     path="/api/developer-web/news/{id}",
     *     summary="Actualizar una noticia",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la noticia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="summary", type="string", minLength=10, maxLength=500),
     *             @OA\Property(property="content", type="string", minLength=50),
     *             @OA\Property(property="featured_image", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="author_id", type="integer", nullable=true),
     *             @OA\Property(property="category", type="string", maxLength=100),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string", maxLength=50), nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}),
     *             @OA\Property(property="published_date", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="seo_title", type="string", maxLength=255, nullable=true),
     *             @OA\Property(property="seo_description", type="string", maxLength=500, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Noticia actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Noticia actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Noticia no encontrada"
     *     )
     * )
     */
    public function update(UpdateNewsApiRequest $request, int $id): JsonResponse
    {
        try {
            $success = $this->newsService->updateNews($id, $request->validated());

            if ($success) {
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
     * @OA\Delete(
     *     path="/api/developer-web/news/{id}",
     *     summary="Eliminar una noticia",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la noticia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Noticia eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Noticia eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Noticia no encontrada"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->newsService->deleteNews($id);

            if ($success) {
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
     * @OA\Get(
     *     path="/api/developer-web/news/stats/summary",
     *     summary="Obtener estadísticas de noticias",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="draft", type="integer", example=25),
     *                 @OA\Property(property="published", type="integer", example=100),
     *                 @OA\Property(property="archived", type="integer", example=25),
     *                 @OA\Property(property="total_views", type="integer", example=125000),
     *                 @OA\Property(property="categories_count", type="integer", example=8),
     *                 @OA\Property(property="published_count", type="integer", example=45)
     *             )
     *         )
     *     )
     * )
     */
    public function getStats(): JsonResponse
    {
        try {
            $statusCounts = $this->newsService->getStatusCounts();
            $categoryCounts = $this->newsService->getCategoryCounts();
            $publishedNews = $this->newsService->getPublishedNews();
            
            $total = array_sum($statusCounts);
            $totalViews = \App\Domains\DeveloperWeb\Models\News::sum('views');

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
     * @OA\Get(
     *     path="/api/developer-web/news/public/categories",
     *     summary="Obtener lista de categorías disponibles",
     *     tags={"News"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="Educación")
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/developer-web/news/{id}/related",
     *     summary="Obtener noticias relacionadas",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la noticia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Límite de noticias relacionadas",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Noticias relacionadas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/News")
     *             )
     *         )
     *     )
     * )
     */
    public function getRelatedNews(int $id, Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 3);
            $relatedNews = $this->newsService->getRelatedNews($id);

            return response()->json([
                'success' => true,
                'data' => array_slice($relatedNews, 0, $limit)
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting related news', [
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
     * @OA\Post(
     *     path="/api/developer-web/news/{id}/reset-views",
     *     summary="Resetear contador de vistas de una noticia",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la noticia",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vistas reseteadas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vistas reseteadas a 0"),
     *             @OA\Property(property="views", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Noticia no encontrada"
     *     )
     * )
     */
    public function resetViews(int $id): JsonResponse
    {
        try {
            $news = $this->newsService->getNewsById($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            $news->update(['views' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Vistas reseteadas a 0',
                'views' => 0
            ]);

        } catch (\Exception $e) {
            Log::error('API Error resetting news views', [
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
}