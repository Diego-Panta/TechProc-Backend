<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Http\Requests\StoreAnnouncementRequest;
use App\Domains\DeveloperWeb\Http\Requests\UpdateAnnouncementRequest;
use App\Domains\DeveloperWeb\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AnnouncementController
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    // Listar anuncios (panel admin)
    public function index(): View
    {
        $filters = [
            'status' => request('status'),
            'target_page' => request('target_page'),
            'display_type' => request('display_type'),
        ];

        $announcements = $this->announcementService->getAllAnnouncements(15, $filters);
        $statusCounts = $this->announcementService->getStatusCounts();
        $displayTypeCounts = $this->announcementService->getDisplayTypeCounts();

        return view('developer-web.announcements.index', compact(
            'announcements',
            'statusCounts',
            'displayTypeCounts',
            'filters'
        ));
    }

    // Mostrar formulario de creación
    public function create(): View
    {
        return view('developer-web.announcements.create');
    }

    // Almacenar nuevo anuncio
    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        try {
            $announcement = $this->announcementService->createAnnouncement($request->validated());

            return redirect()->route('developer-web.announcements.index')
                ->with('success', 'Anuncio creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear anuncio', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear el anuncio: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Mostrar detalles de anuncio
    public function show(int $id): View
    {
        $announcement = $this->announcementService->getAnnouncementById($id);

        if (!$announcement) {
            abort(404);
        }

        return view('developer-web.announcements.show', compact('announcement'));
    }

    // Mostrar formulario de edición
    public function edit(int $id): View
    {
        $announcement = $this->announcementService->getAnnouncementById($id);

        if (!$announcement) {
            abort(404);
        }

        return view('developer-web.announcements.edit', compact('announcement'));
    }

    // Actualizar anuncio
    public function update(UpdateAnnouncementRequest $request, int $id): RedirectResponse
    {
        try {
            $success = $this->announcementService->updateAnnouncement($id, $request->validated());

            if ($success) {
                return redirect()->route('developer-web.announcements.index')
                    ->with('success', 'Anuncio actualizado exitosamente.');
            }

            return redirect()->route('developer-web.announcements.index')
                ->with('error', 'No se pudo actualizar el anuncio.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar anuncio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar el anuncio: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Eliminar anuncio
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
            Log::error('Error al eliminar anuncio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el anuncio'
            ], 500);
        }
    }

    // API para frontend - Listar anuncios activos
    public function apiIndex(): JsonResponse
    {
        try {
            $filters = [
                'target_page' => request('target_page'),
                'display_type' => request('display_type'),
            ];

            $announcements = $this->announcementService->getAnnouncementsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => $announcements
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de anuncios', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los anuncios'
            ], 500);
        }
    }

    // API para frontend - Mostrar anuncio específico
    public function apiShow(int $id): JsonResponse
    {
        try {
            $announcement = $this->announcementService->getAnnouncementById($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anuncio no encontrado'
                ], 404);
            }

            // Incrementar vistas
            $this->announcementService->incrementViews($id);

            return response()->json([
                'success' => true,
                'data' => $announcement
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de anuncio específico', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el anuncio'
            ], 500);
        }
    }

    // Mostrar listado público de anuncios activos
    public function publicIndex(): View
    {
        $announcements = $this->announcementService->getActiveAnnouncements();

        return view('developer-web.announcements.public-index', compact('announcements'));
    }

    // Mostrar anuncio específico (público) e incrementar vistas
    public function publicShow(int $id): View
    {
        $announcement = $this->announcementService->getAnnouncementById($id);

        if (!$announcement) {
            abort(404);
        }

        // Incrementar vistas solo si el anuncio está publicado y activo
        if (
            $announcement->status === 'published' &&
            $announcement->start_date <= now() &&
            $announcement->end_date >= now()
        ) {
            $this->announcementService->incrementViews($id);
        }

        return view('developer-web.announcements.public-show', compact('announcement'));
    }

    // Resetear vistas (para testing)
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
            Log::error('Error al resetear vistas', [
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
