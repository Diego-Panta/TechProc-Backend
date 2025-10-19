<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Http\Requests\Api\StoreAnnouncementApiRequest;
use App\Domains\DeveloperWeb\Http\Requests\Api\UpdateAnnouncementApiRequest;
use App\Domains\DeveloperWeb\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnouncementApiController
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/developer-web/announcements",
     *     summary="Listar anuncios (admin)",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrar por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "published", "archived"})
     *     ),
     *     @OA\Parameter(
     *         name="target_page",
     *         in="query",
     *         description="Filtrar por página objetivo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="display_type",
     *         in="query",
     *         description="Filtrar por tipo de visualización",
     *         required=false,
     *         @OA\Schema(type="string", enum={"banner", "modal", "popup", "notification"})
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
     *         description="Lista de anuncios",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Announcement")
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
                'target_page' => $request->get('target_page'),
                'display_type' => $request->get('display_type'),
            ];

            $perPage = $request->get('per_page', 15);

            $announcements = $this->announcementService->getAllAnnouncements($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $announcements
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing announcements', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los anuncios',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/announcements/public",
     *     summary="Listar anuncios públicos activos",
     *     tags={"Announcements"},
     *     @OA\Parameter(
     *         name="target_page",
     *         in="query",
     *         description="Filtrar por página objetivo",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="display_type",
     *         in="query",
     *         description="Filtrar por tipo de visualización",
     *         required=false,
     *         @OA\Schema(type="string", enum={"banner", "modal", "popup", "notification"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de anuncios públicos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Announcement")
     *             )
     *         )
     *     )
     * )
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $filters = [
                'target_page' => $request->get('target_page'),
                'display_type' => $request->get('display_type'),
            ];

            $announcements = $this->announcementService->getActiveAnnouncements();

            // Aplicar filtros adicionales
            if (!empty($filters['target_page'])) {
                $announcements = $announcements->filter(function ($announcement) use ($filters) {
                    return $announcement->target_page === $filters['target_page'];
                });
            }

            if (!empty($filters['display_type'])) {
                $announcements = $announcements->filter(function ($announcement) use ($filters) {
                    return $announcement->display_type === $filters['display_type'];
                });
            }

            return response()->json([
                'success' => true,
                'data' => $announcements->values()
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing public announcements', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los anuncios públicos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/announcements/{id}",
     *     summary="Obtener detalles de un anuncio",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del anuncio",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del anuncio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Anuncio no encontrado"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $announcement = $this->announcementService->getAnnouncementById($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $announcement
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing announcement', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el anuncio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/announcements/public/{id}",
     *     summary="Obtener detalles de un anuncio público",
     *     tags={"Announcements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del anuncio",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del anuncio público",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Anuncio no encontrado"
     *     )
     * )
     */
    public function publicShow(int $id): JsonResponse
    {
        try {
            $announcement = $this->announcementService->getAnnouncementById($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no encontrado'
                ], 404);
            }

            // Verificar que el anuncio esté activo y publicado
            $now = now();
            if ($announcement->status !== 'published' || 
                $announcement->start_date > $now || 
                $announcement->end_date < $now) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no disponible'
                ], 404);
            }

            // Incrementar vistas
            $this->announcementService->incrementViews($id);

            return response()->json([
                'success' => true,
                'data' => $announcement
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing public announcement', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el anuncio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/announcements",
     *     summary="Crear un nuevo anuncio",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "display_type", "target_page", "status", "start_date", "end_date"},
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="content", type="string", minLength=10),
     *             @OA\Property(property="image_url", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="display_type", type="string", enum={"banner", "modal", "popup", "notification"}),
     *             @OA\Property(property="target_page", type="string", maxLength=100),
     *             @OA\Property(property="link_url", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="button_text", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="end_date", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Anuncio creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="message", type="string", example="Anuncio creado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(StoreAnnouncementApiRequest $request): JsonResponse
    {
        try {
            $announcement = $this->announcementService->createAnnouncement($request->validated());

            return response()->json([
                'success' => true,
                'data' => $announcement,
                'message' => 'Anuncio creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating announcement', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el anuncio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/developer-web/announcements/{id}",
     *     summary="Actualizar un anuncio",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del anuncio",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255),
     *             @OA\Property(property="content", type="string", minLength=10),
     *             @OA\Property(property="image_url", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="display_type", type="string", enum={"banner", "modal", "popup", "notification"}),
     *             @OA\Property(property="target_page", type="string", maxLength=100),
     *             @OA\Property(property="link_url", type="string", format="url", maxLength=500, nullable=true),
     *             @OA\Property(property="button_text", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="end_date", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Anuncio actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Anuncio actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Anuncio no encontrado"
     *     )
     * )
     */
    public function update(UpdateAnnouncementApiRequest $request, int $id): JsonResponse
    {
        try {
            $success = $this->announcementService->updateAnnouncement($id, $request->validated());

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anuncio actualizado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar el anuncio'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error updating announcement', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el anuncio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/developer-web/announcements/{id}",
     *     summary="Eliminar un anuncio",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del anuncio",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Anuncio eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Anuncio eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Anuncio no encontrado"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->announcementService->deleteAnnouncement($id);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anuncio eliminado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el anuncio'
            ], 404);

        } catch (\Exception $e) {
            Log::error('API Error deleting announcement', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el anuncio',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/announcements/stats/summary",
     *     summary="Obtener estadísticas de anuncios",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="draft", type="integer", example=5),
     *                 @OA\Property(property="published", type="integer", example=15),
     *                 @OA\Property(property="archived", type="integer", example=5),
     *                 @OA\Property(property="total_views", type="integer", example=12500),
     *                 @OA\Property(property="active_count", type="integer", example=8)
     *             )
     *         )
     *     )
     * )
     */
    public function getStats(): JsonResponse
    {
        try {
            $statusCounts = $this->announcementService->getStatusCounts();
            $activeAnnouncements = $this->announcementService->getActiveAnnouncements();
            
            $total = array_sum($statusCounts);
            $totalViews = \App\Domains\DeveloperWeb\Models\Announcement::sum('views');

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'draft' => $statusCounts['draft'] ?? 0,
                    'published' => $statusCounts['published'] ?? 0,
                    'archived' => $statusCounts['archived'] ?? 0,
                    'total_views' => $totalViews,
                    'active_count' => $activeAnnouncements->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting announcement stats', [
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
     * @OA\Post(
     *     path="/api/developer-web/announcements/{id}/reset-views",
     *     summary="Resetear contador de vistas de un anuncio",
     *     tags={"Announcements"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del anuncio",
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
     *         description="Anuncio no encontrado"
     *     )
     * )
     */
    public function resetViews(int $id): JsonResponse
    {
        try {
            $announcement = $this->announcementService->getAnnouncementById($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no encontrado'
                ], 404);
            }

            $announcement->update(['views' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Vistas reseteadas a 0',
                'views' => 0
            ]);

        } catch (\Exception $e) {
            Log::error('API Error resetting announcement views', [
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