<?php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api\ContentTypes;

use App\Domains\DeveloperWeb\Services\ContentTypes\AnnouncementService;
use App\Domains\DeveloperWeb\Http\Requests\Api\ContentTypes\StoreAnnouncementRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnouncementApiController
{
    public function __construct(
        protected AnnouncementService $announcementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'target_page', 'search']);
            $perPage = $request->get('per_page', 15);

            $announcements = $this->announcementService->getAll($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $announcements
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing announcements', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los anuncios'
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $announcement = $this->announcementService->getById($id);

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
            Log::error('Error showing announcement', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el anuncio'
            ], 500);
        }
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        try {
            $announcement = $this->announcementService->create($request->validated());

            Log::info('Anuncio creado', ['id' => $announcement->id, 'title' => $announcement->title]);

            return response()->json([
                'success' => true,
                'data' => $announcement,
                'message' => 'Anuncio creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating announcement', ['data' => $request->all(), 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el anuncio'
            ], 500);
        }
    }

    public function update(StoreAnnouncementRequest $request, int $id): JsonResponse
    {
        try {
            $success = $this->announcementService->update($id, $request->validated());

            if ($success) {
                Log::info('Anuncio actualizado', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Anuncio actualizado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Anuncio no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error updating announcement', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el anuncio'
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->announcementService->delete($id);

            if ($success) {
                Log::info('Anuncio eliminado', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Anuncio eliminado exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Anuncio no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting announcement', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el anuncio'
            ], 500);
        }
    }

    // MÉTODOS ESPECÍFICOS SOLICITADOS
    
    public function getPublished(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $announcements = $this->announcementService->getPublishedAnnouncements($perPage);

            return response()->json([
                'success' => true,
                'data' => $announcements
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting published announcements', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener anuncios publicados'
            ], 500);
        }
    }

    public function resetViews(int $id): JsonResponse
    {
        try {
            $success = $this->announcementService->resetViews($id);

            if ($success) {
                Log::info('Vistas de anuncio reseteadas', ['id' => $id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Vistas reseteadas exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Anuncio no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error resetting announcement views', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al resetear vistas'
            ], 500);
        }
    }
}