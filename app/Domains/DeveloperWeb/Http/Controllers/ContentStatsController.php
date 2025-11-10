<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Repositories\ContentItemRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContentStatsController
{
    public function __construct(
        protected ContentItemRepository $contentItemRepository
    ) {}

    /**
     * Obtener estadísticas generales de todos los tipos de contenido
     */
    public function getOverallStats(): JsonResponse
    {
        try {
            $stats = $this->contentItemRepository->getOverallStats();

            Log::info('Estadísticas generales de contenido consultadas');

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas generales de contenido', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas generales',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}