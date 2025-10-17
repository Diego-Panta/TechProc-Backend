<?php
// app/Domains/DeveloperWeb/Http/Controllers/Api/ChatbotFaqApiController.php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Services\ChatbotFaqService;
use App\Domains\DeveloperWeb\Http\Requests\Api\StoreFaqApiRequest;
use App\Domains\DeveloperWeb\Http\Requests\Api\UpdateFaqApiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotFaqApiController
{
    public function __construct(
        private ChatbotFaqService $faqService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs",
     *     summary="Listar FAQs (admin)",
     *     tags={"Chatbot FAQs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrar por estado activo",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar en preguntas y respuestas",
     *         required=false,
     *         @OA\Schema(type="string")
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
     *         description="Lista de FAQs",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/ChatbotFaq")
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
                'category' => $request->get('category'),
                'active' => $request->get('active'),
                'search' => $request->get('search'),
            ];

            $perPage = $request->get('per_page', 15);

            $faqs = $this->faqService->getAllFaqs($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing FAQs', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las FAQs',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs/public",
     *     summary="Listar FAQs públicas activas",
     *     tags={"Chatbot FAQs"},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filtrar por categoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar en preguntas y respuestas",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de FAQs públicas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatbotFaq")
     *             )
     *         )
     *     )
     * )
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $filters = [
                'category' => $request->get('category'),
                'search' => $request->get('search'),
            ];

            $faqs = $this->faqService->getFaqsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);

        } catch (\Exception $e) {
            Log::error('API Error listing public FAQs', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las FAQs públicas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs/{id}",
     *     summary="Obtener detalles de una FAQ",
     *     tags={"Chatbot FAQs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la FAQ",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la FAQ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ChatbotFaq")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FAQ no encontrada"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $faq = $this->faqService->getFaqById($id);

            if (!$faq) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $faq
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing FAQ', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la FAQ',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs/public/{id}",
     *     summary="Obtener detalles de una FAQ pública",
     *     tags={"Chatbot FAQs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la FAQ",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la FAQ pública",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ChatbotFaq")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FAQ no encontrada"
     *     )
     * )
     */
    public function publicShow(int $id): JsonResponse
    {
        try {
            $faq = $this->faqService->getFaqById($id);

            if (!$faq) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ no encontrada'
                ], 404);
            }

            // Verificar que la FAQ esté activa
            if (!$faq->active) {
                return response()->json([
                    'success' => false,
                    'message' => 'FAQ no disponible'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $faq
            ]);

        } catch (\Exception $e) {
            Log::error('API Error showing public FAQ', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la FAQ',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/chatbot/faqs",
     *     summary="Crear una nueva FAQ",
     *     tags={"Chatbot FAQs"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question", "answer", "category"},
     *             @OA\Property(property="question", type="string", maxLength=1000),
     *             @OA\Property(property="answer", type="string", maxLength=5000),
     *             @OA\Property(property="category", type="string", maxLength=100),
     *             @OA\Property(property="new_category", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="keywords", type="array", @OA\Items(type="string", maxLength=50)),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="FAQ creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ChatbotFaq"),
     *             @OA\Property(property="message", type="string", example="FAQ creada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(StoreFaqApiRequest $request): JsonResponse
    {
        try {
            $faq = $this->faqService->createFaq($request->validated());

            return response()->json([
                'success' => true,
                'data' => $faq,
                'message' => 'FAQ creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('API Error creating FAQ', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la FAQ',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/developer-web/chatbot/faqs/{id}",
     *     summary="Actualizar una FAQ",
     *     tags={"Chatbot FAQs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la FAQ",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question", type="string", maxLength=1000),
     *             @OA\Property(property="answer", type="string", maxLength=5000),
     *             @OA\Property(property="category", type="string", maxLength=100),
     *             @OA\Property(property="new_category", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="keywords", type="array", @OA\Items(type="string", maxLength=50)),
     *             @OA\Property(property="active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="FAQ actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FAQ no encontrada"
     *     )
     * )
     */
    public function update(UpdateFaqApiRequest $request, int $id): JsonResponse
    {
        try {
            $success = $this->faqService->updateFaq($id, $request->validated());

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'FAQ actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la FAQ'
            ], 400);

        } catch (\Exception $e) {
            Log::error('API Error updating FAQ', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la FAQ',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/developer-web/chatbot/faqs/{id}",
     *     summary="Eliminar una FAQ",
     *     tags={"Chatbot FAQs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la FAQ",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="FAQ eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FAQ no encontrada"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $success = $this->faqService->deleteFaq($id);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'FAQ eliminada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la FAQ'
            ], 404);

        } catch (\Exception $e) {
            Log::error('API Error deleting FAQ', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la FAQ',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs/categories",
     *     summary="Obtener lista de categorías de FAQs",
     *     tags={"Chatbot FAQs"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorías",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="Atención al Cliente")
     *             )
     *         )
     *     )
     * )
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->faqService->getCategories();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting FAQ categories', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs/stats/summary",
     *     summary="Obtener estadísticas de FAQs",
     *     tags={"Chatbot FAQs"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="active", type="integer", example=45),
     *                 @OA\Property(property="inactive", type="integer", example=5),
     *                 @OA\Property(property="total_usage", type="integer", example=1250),
     *                 @OA\Property(property="categories_count", type="integer", example=8)
     *             )
     *         )
     *     )
     * )
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalFaqs = $this->faqService->getTotalFaqs();
            $activeFaqsCount = $this->faqService->getActiveFaqsCount();
            $categories = $this->faqService->getCategories();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalFaqs,
                    'active' => $activeFaqsCount,
                    'inactive' => $totalFaqs - $activeFaqsCount,
                    'categories_count' => count($categories),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting FAQ stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}