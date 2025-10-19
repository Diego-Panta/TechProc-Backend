<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Http\Requests\StoreNewsRequest;
use App\Domains\DeveloperWeb\Http\Requests\UpdateNewsRequest;
use App\Domains\DeveloperWeb\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class NewsController
{
    public function __construct(
        private NewsService $newsService
    ) {}

    // Listar noticias (panel admin)
    public function index(): View
    {
        $filters = [
            'status' => request('status'),
            'category' => request('category'),
            'search' => request('search'),
        ];

        $news = $this->newsService->getAllNews(15, $filters);
        $statusCounts = $this->newsService->getStatusCounts();
        $categoryCounts = $this->newsService->getCategoryCounts();

        return view('developer-web.news.index', compact(
            'news',
            'statusCounts',
            'categoryCounts',
            'filters'
        ));
    }

    // Mostrar formulario de creación
    public function create(): View
    {
        return view('developer-web.news.create');
    }

    // Almacenar nueva noticia
    public function store(StoreNewsRequest $request): RedirectResponse
    {
        try {
            $news = $this->newsService->createNews($request->validated());

            return redirect()->route('developer-web.news.index')
                ->with('success', 'Noticia creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear noticia', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la noticia: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Mostrar detalles de noticia
    public function show(int $id): View
    {
        $news = $this->newsService->getNewsById($id);

        if (!$news) {
            abort(404);
        }

        return view('developer-web.news.show', compact('news'));
    }

    // Mostrar formulario de edición
    public function edit(int $id): View
    {
        $news = $this->newsService->getNewsById($id);

        if (!$news) {
            abort(404);
        }

        return view('developer-web.news.edit', compact('news'));
    }

    // Actualizar noticia
    public function update(UpdateNewsRequest $request, int $id): RedirectResponse
    {
        try {
            $success = $this->newsService->updateNews($id, $request->validated());

            if ($success) {
                return redirect()->route('developer-web.news.index')
                    ->with('success', 'Noticia actualizada exitosamente.');
            }

            return redirect()->route('developer-web.news.index')
                ->with('error', 'No se pudo actualizar la noticia.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar noticia', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar la noticia: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Eliminar noticia
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
            Log::error('Error al eliminar noticia', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la noticia'
            ], 500);
        }
    }

    // API para frontend - Listar noticias publicadas
    public function apiIndex(): JsonResponse
    {
        try {
            $filters = [
                'category' => request('category'),
                'search' => request('search'),
                'limit' => request('limit', 10),
            ];

            $news = $this->newsService->getNewsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'news' => $news,
                    'pagination' => [
                        'current_page' => 1, // Para simplificar, en una implementación real usarías LengthAwarePaginator
                        'total_pages' => ceil(count($news) / $filters['limit']),
                        'total_records' => count($news),
                        'per_page' => $filters['limit']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de noticias', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las noticias'
            ], 500);
        }
    }

    // API para frontend - Mostrar noticia específica por ID
    public function apiShow(int $id): JsonResponse
    {
        try {
            $news = $this->newsService->getNewsById($id);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            // Incrementar vistas solo si está publicada
            if ($news->status === 'published') {
                $this->newsService->incrementViews($id);
            }

            return response()->json([
                'success' => true,
                'data' => $news
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de noticia específica', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia'
            ], 500);
        }
    }

    // API para frontend - Mostrar noticia por slug
    public function apiShowBySlug(string $slug): JsonResponse
    {
        try {
            $news = $this->newsService->getNewsBySlug($slug);

            if (!$news) {
                return response()->json([
                    'success' => false,
                    'message' => 'Noticia no encontrada'
                ], 404);
            }

            // Incrementar vistas solo si está publicada
            if ($news->status === 'published') {
                $this->newsService->incrementViews($news->id);
            }

            return response()->json([
                'success' => true,
                'data' => $news
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de noticia por slug', [
                'slug' => $slug,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la noticia'
            ], 500);
        }
    }

    // Mostrar listado público de noticias
    public function publicIndex(): View
    {
        $news = $this->newsService->getPublishedNews();

        return view('developer-web.news.public-index', compact('news'));
    }

    // Mostrar noticia específica (pública) por ID e incrementar vistas
    public function publicShow(int $id): View
    {
        $news = $this->newsService->getNewsById($id);

        if (!$news || $news->status !== 'published') {
            abort(404);
        }

        // Incrementar vistas
        $this->newsService->incrementViews($id);

        // Obtener noticias relacionadas
        $relatedNews = $this->newsService->getRelatedNews($id);

        return view('developer-web.news.public-show', compact('news', 'relatedNews'));
    }

    // Mostrar noticia específica (pública) por slug
    public function publicShowBySlug(string $slug): View
    {
        $news = $this->newsService->getNewsBySlug($slug);

        if (!$news || $news->status !== 'published') {
            abort(404);
        }

        // Incrementar vistas
        $this->newsService->incrementViews($news->id);

        // Obtener noticias relacionadas
        $relatedNews = $this->newsService->getRelatedNews($news->id);

        return view('developer-web.news.public-show', compact('news', 'relatedNews'));
    }

    // Resetear vistas (para testing)
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
            Log::error('Error al resetear vistas de noticia', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al resetear vistas'
            ], 500);
        }
    }
}