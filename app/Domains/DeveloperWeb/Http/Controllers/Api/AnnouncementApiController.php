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
     * Listar anuncios (PROTEGIDO)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado desde el middleware
            $user = $request->user();
            
            $filters = [
                'status' => $request->get('status'),
                'target_page' => $request->get('target_page'),
                'display_type' => $request->get('display_type'),
            ];

            $perPage = $request->get('per_page', 15);

            $announcements = $this->announcementService->getAllAnnouncements($perPage, $filters);

            // Log de acceso
            Log::info('Usuario accedió a listado de anuncios', [
                'user_id' => $user->id,
                'email' => $user->email,
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $announcements
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing announcements', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Obtener anuncio específico (PROTEGIDO)
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $announcement = $this->announcementService->getAnnouncementById($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no encontrado'
                ], 404);
            }

            Log::info('Usuario accedió a anuncio específico', [
                'user_id' => $user->id,
                'announcement_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'data' => $announcement
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing announcement', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Crear nuevo anuncio (PROTEGIDO)
     */
    public function store(StoreAnnouncementApiRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Pasar el ID del usuario autenticado al servicio
            $announcement = $this->announcementService->createAnnouncement(
                $request->validated(), 
                $user->id
            );

            // Log de creación
            Log::info('Usuario creó nuevo anuncio', [
                'user_id' => $user->id,
                'announcement_id' => $announcement->id,
                'title' => $announcement->title
            ]);

            return response()->json([
                'success' => true,
                'data' => $announcement,
                'message' => 'Anuncio creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating announcement', [
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Actualizar anuncio (PROTEGIDO)
     */
    public function update(UpdateAnnouncementApiRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $success = $this->announcementService->updateAnnouncement($id, $request->validated());

            if ($success) {
                Log::info('Usuario actualizó anuncio', [
                    'user_id' => $user->id,
                    'announcement_id' => $id
                ]);

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
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Eliminar anuncio (PROTEGIDO)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $success = $this->announcementService->deleteAnnouncement($id);

            if ($success) {
                Log::info('Usuario eliminó anuncio', [
                    'user_id' => $user->id,
                    'announcement_id' => $id
                ]);

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
                'user_id' => $request->user()->id ?? 'unknown',
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
     * Obtener estadísticas (PROTEGIDO)
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $statusCounts = $this->announcementService->getStatusCounts();
            $activeAnnouncements = $this->announcementService->getActiveAnnouncements();
            
            $total = array_sum($statusCounts);
            $totalViews = \App\Domains\DeveloperWeb\Models\Announcement::sum('views');

            Log::info('Usuario accedió a estadísticas de anuncios', [
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
                    'active_count' => $activeAnnouncements->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting announcement stats', [
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
     * Resetear vistas (PROTEGIDO)
     */
    public function resetViews(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $announcement = $this->announcementService->getAnnouncementById($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no encontrado'
                ], 404);
            }

            $announcement->update(['views' => 0]);

            Log::info('Usuario reseteó vistas de anuncio', [
                'user_id' => $user->id,
                'announcement_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vistas reseteadas a 0',
                'views' => 0
            ]);

        } catch (\Exception $e) {
            Log::error('API Error resetting announcement views', [
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
}