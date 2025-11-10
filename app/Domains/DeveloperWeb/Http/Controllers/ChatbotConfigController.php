<?php
// app/Domains/DeveloperWeb/Http/Controllers/Api/ChatbotConfigController.php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Services\ChatbotConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotConfigController
{
    public function __construct(
        private ChatbotConfigService $configService
    ) {}

    public function getConfig(): JsonResponse
    {
        try {
            $config = $this->configService->getConfig();

            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting chatbot config', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateConfig(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'enabled' => 'sometimes|boolean',
                'greeting_message' => 'sometimes|string|max:500',
                'fallback_message' => 'sometimes|string|max:500',
                'response_delay' => 'sometimes|integer|min:0|max:10000',
                'max_conversations_per_day' => 'sometimes|integer|min:0',
                'contact_threshold' => 'sometimes|integer|min:1|max:10',
            ]);

            $success = $this->configService->updateConfig($validated);

            if ($success) {
                $currentConfig = $this->configService->getConfig();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración actualizada correctamente',
                    'data' => [
                        'updated_at' => $currentConfig['updated_at']
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la configuración'
            ], 500);

        } catch (\Exception $e) {
            Log::error('API Error updating chatbot config', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function resetConfig(): JsonResponse
    {
        try {
            $success = $this->configService->resetToDefault();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración restaurada a valores por defecto'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo resetear la configuración'
            ], 500);

        } catch (\Exception $e) {
            Log::error('API Error resetting chatbot config', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al resetear la configuración',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function healthCheck(): JsonResponse
    {
        try {
            $healthStatus = $this->configService->getHealthStatus();

            return response()->json([
                'success' => true,
                'data' => $healthStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servicio de configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}