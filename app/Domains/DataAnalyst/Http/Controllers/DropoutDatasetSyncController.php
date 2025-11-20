<?php
// app/Domains/DataAnalyst/Http/Controllers/DropoutDatasetSyncController.php

namespace App\Domains\DataAnalyst\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Domains\DataAnalyst\Services\DropoutDatasetSyncService;

class DropoutDatasetSyncController extends Controller
{
    private DropoutDatasetSyncService $syncService;

    public function __construct(DropoutDatasetSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Sincronizar dataset completo
     */
    public function syncDataset(Request $request): JsonResponse
    {
        try {
            $result = $this->syncService->syncPredictionDataset();

            return response()->json([
                'success' => $result['success'],
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error sincronizando dataset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estado de la sincronización
     */
    public function getSyncStatus(): JsonResponse
    {
        try {
            $status = $this->syncService->getSyncStatus();

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar dataset local (solo para testing)
     */
    public function generateLocalDataset(): JsonResponse
    {
        try {
            $dataset = $this->syncService->generateCurrentPredictionDataset();

            return response()->json([
                'success' => true,
                'data' => [
                    'records_count' => count($dataset),
                    'sample_records' => array_slice($dataset, 0, 3)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error generando dataset: ' . $e->getMessage()
            ], 500);
        }
    }
}