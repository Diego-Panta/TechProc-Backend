<?php

namespace App\Domains\Security\Http\Controllers;

use App\Domains\Security\Resources\UserBlockResource;
use App\Domains\Security\Services\UserBlockService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserBlockController extends Controller
{
    public function __construct(
        private UserBlockService $blockService
    ) {}

    /**
     * Listar usuarios bloqueados actualmente
     */
    public function index(Request $request): JsonResponse
    {
        // Verificar permiso
        if (!$request->user()->hasPermissionTo('user-blocks.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver los usuarios bloqueados'
            ], 403);
        }

        $perPage = $request->get('per_page', 15);
        $blocks = $this->blockService->getAllBlockedUsers($perPage);

        return response()->json([
            'success' => true,
            'data' => UserBlockResource::collection($blocks),
            'meta' => [
                'total' => $blocks->total(),
                'per_page' => $blocks->perPage(),
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
            ]
        ]);
    }

    /**
     * Obtener historial de bloqueos
     */
    public function history(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver el historial de bloqueos'
            ], 403);
        }

        $perPage = $request->get('per_page', 15);
        $blocks = $this->blockService->getBlockHistory($perPage);

        return response()->json([
            'success' => true,
            'data' => UserBlockResource::collection($blocks),
            'meta' => [
                'total' => $blocks->total(),
                'per_page' => $blocks->perPage(),
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
            ]
        ]);
    }

    /**
     * Obtener historial de bloqueos de un usuario específico
     */
    public function userHistory(Request $request, int $userId): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver el historial de bloqueos'
            ], 403);
        }

        $blocks = $this->blockService->getUserBlockHistory($userId);

        return response()->json([
            'success' => true,
            'data' => UserBlockResource::collection($blocks),
        ]);
    }

    /**
     * Bloquear un usuario manualmente
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.create')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para bloquear usuarios'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|max:500',
            'duration_minutes' => 'nullable|integer|min:1', // null = permanente
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no se esté bloqueando a sí mismo
        if ($request->user_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes bloquearte a ti mismo'
            ], 400);
        }

        // Verificar que el usuario no esté ya bloqueado
        if ($this->blockService->isUserBlocked($request->user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario ya está bloqueado'
            ], 400);
        }

        try {
            $block = $this->blockService->blockUserManually(
                userId: $request->user_id,
                blockedBy: $request->user()->id,
                reason: $request->reason,
                durationMinutes: $request->duration_minutes,
                ip: $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Usuario bloqueado exitosamente',
                'data' => new UserBlockResource($block->load(['user', 'blockedByUser']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al bloquear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un bloqueo
     */
    public function show(Request $request, int $blockId): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver los bloqueos'
            ], 403);
        }

        $block = $this->blockService->getActiveBlock($blockId);

        if (!$block) {
            return response()->json([
                'success' => false,
                'message' => 'Bloqueo no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserBlockResource($block->load(['user', 'blockedByUser', 'unblockedByUser']))
        ]);
    }

    /**
     * Desbloquear un usuario
     */
    public function destroy(Request $request, int $userId): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.delete')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para desbloquear usuarios'
            ], 403);
        }

        $result = $this->blockService->unblockUser(
            userId: $userId,
            unblockedBy: $request->user()->id,
            ip: $request->ip()
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Desbloquear por ID de bloqueo
     */
    public function unblockById(Request $request, int $blockId): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.delete')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para desbloquear usuarios'
            ], 403);
        }

        $result = $this->blockService->unblockById(
            blockId: $blockId,
            unblockedBy: $request->user()->id,
            ip: $request->ip()
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Obtener estadísticas de bloqueos
     */
    public function statistics(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver las estadísticas'
            ], 403);
        }

        $days = $request->get('days', 30);
        $statistics = $this->blockService->getStatistics($days);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Verificar si un usuario está bloqueado
     */
    public function checkBlock(Request $request, int $userId): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('user-blocks.view-any')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para verificar bloqueos'
            ], 403);
        }

        $block = $this->blockService->getActiveBlock($userId);

        if (!$block || !$block->is_currently_blocked) {
            return response()->json([
                'success' => true,
                'blocked' => false,
                'message' => 'El usuario no está bloqueado'
            ]);
        }

        return response()->json([
            'success' => true,
            'blocked' => true,
            'data' => new UserBlockResource($block)
        ]);
    }
}
