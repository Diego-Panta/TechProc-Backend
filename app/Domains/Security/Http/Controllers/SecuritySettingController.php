<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Resources\SecuritySettingResource;
use App\Domains\Security\Services\SecuritySettingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SecuritySettingController extends Controller
{
    public function __construct(
        private SecuritySettingService $settingService
    ) {}

    /**
     * Listar todas las configuraciones de seguridad
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.view')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las configuraciones de seguridad'
            ], 403);
        }

        $settings = $this->settingService->getAllSettings();

        return response()->json([
            'success' => true,
            'data' => SecuritySettingResource::collection($settings)
        ]);
    }

    /**
     * Obtener configuraciones agrupadas
     */
    public function grouped(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.view')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las configuraciones de seguridad'
            ], 403);
        }

        $settings = $this->settingService->getAllSettingsGrouped();

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Obtener configuraciones por grupo
     */
    public function byGroup(Request $request, string $group): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.view')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las configuraciones de seguridad'
            ], 403);
        }

        $settings = $this->settingService->getSettingsByGroup($group);

        return response()->json([
            'success' => true,
            'data' => SecuritySettingResource::collection($settings)
        ]);
    }

    /**
     * Obtener configuraciones de login/bloqueo
     */
    public function loginSettings(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.view')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las configuraciones de seguridad'
            ], 403);
        }

        $settings = $this->settingService->getLoginSettings();

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Actualizar una configuración específica
     */
    public function update(Request $request, string $key): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.update')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar las configuraciones de seguridad'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->settingService->update($key, $request->value);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateMany(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.update')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar las configuraciones de seguridad'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->settingService->updateMany($request->settings);

        return response()->json($result);
    }

    /**
     * Limpiar cache de configuraciones
     */
    public function clearCache(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('security-settings.update')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para gestionar el cache de configuraciones'
            ], 403);
        }

        $this->settingService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Cache de configuraciones limpiado'
        ]);
    }
}
