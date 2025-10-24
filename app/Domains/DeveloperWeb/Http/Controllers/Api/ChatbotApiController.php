<?php
// app/Domains/DeveloperWeb/Http/Controllers/Api/ChatbotApiController.php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Services\GeminiChatbotService;
use App\Domains\DeveloperWeb\Services\ChatbotFaqService;
use App\Domains\DeveloperWeb\Http\Requests\Api\ChatbotMessageApiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotApiController
{
    public function __construct(
        private GeminiChatbotService $chatbotService,
        private ChatbotFaqService $faqService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/developer-web/chatbot/conversation/start",
     *     summary="Iniciar una nueva conversación con el chatbot",
     *     tags={"Chatbot"},
     *     @OA\Response(
     *         response=200,
     *         description="Conversación iniciada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="conversation_id", type="integer", example=1),
     *                 @OA\Property(property="welcome_message", type="string", example="¡Hola! Soy tu asistente virtual...")
     *             )
     *         )
     *     )
     * )
     */
    public function startConversation(): JsonResponse
    {
        try {
            $conversation = $this->chatbotService->startConversation();

            return response()->json([
                'success' => true,
                'data' => $conversation
            ]);

        } catch (\Exception $e) {
            Log::error('API Error starting chatbot conversation', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar la conversación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/chatbot/conversation/message",
     *     summary="Enviar mensaje al chatbot",
     *     tags={"Chatbot"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message", "conversation_id"},
     *             @OA\Property(property="message", type="string", maxLength=1000),
     *             @OA\Property(property="conversation_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mensaje procesado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="response", type="string"),
     *                 @OA\Property(property="source", type="string", enum={"faq", "gemini", "fallback"}),
     *                 @OA\Property(property="conversation_id", type="integer"),
     *                 @OA\Property(property="faq_id", type="integer", nullable=true)
     *             )
     *         )
     *     )
     * )
     */
    public function sendMessage(ChatbotMessageApiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $response = $this->chatbotService->processMessage(
                $validated['message'],
                $validated['conversation_id']
            );

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('API Error sending chatbot message', [
                'conversation_id' => $request->conversation_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el mensaje',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/developer-web/chatbot/conversation/end",
     *     summary="Finalizar conversación con el chatbot",
     *     tags={"Chatbot"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"conversation_id"},
     *             @OA\Property(property="conversation_id", type="integer"),
     *             @OA\Property(property="feedback", type="object",
     *                 @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *                 @OA\Property(property="comment", type="string", maxLength=500),
     *                 @OA\Property(property="resolved", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conversación finalizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conversación finalizada")
     *         )
     *     )
     * )
     */
    public function endConversation(ChatbotMessageApiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $success = $this->chatbotService->endConversation(
                $validated['conversation_id'],
                $request->input('feedback', [])
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conversación finalizada'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo finalizar la conversación'
            ], 500);

        } catch (\Exception $e) {
            Log::error('API Error ending chatbot conversation', [
                'conversation_id' => $request->conversation_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la conversación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/faqs/category/{category}",
     *     summary="Obtener FAQs por categoría",
     *     tags={"Chatbot"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Categoría de FAQs (usar 'all' para todas)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de FAQs por categoría",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/ChatbotFaq")
     *             )
     *         )
     *     )
     * )
     */
    public function getFaqsByCategory(string $category = null): JsonResponse
    {
        try {
            $filters = ['active' => true];
            
            if ($category && $category !== 'all') {
                $filters['category'] = $category;
            }
            
            $faqs = $this->faqService->getFaqsForPublic($filters);
            
            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            Log::error('API Error getting FAQs by category', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preguntas frecuentes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Métodos protegidos para analytics (requieren autenticación)

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/conversations",
     *     summary="Obtener lista de conversaciones (admin)",
     *     tags={"Chatbot Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página para paginación",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de conversaciones",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/ChatbotConversation")
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             )
     *         )
     *     )
     * )
     */
    /*public function getConversations(Request $request): JsonResponse
    {
        try {
            // Implementar lógica para obtener conversaciones paginadas
            // Esto requeriría un nuevo método en el servicio/repository
            $perPage = $request->get('per_page', 15);
            
            // Placeholder - necesitarías implementar este método
            $conversations = []; // $this->chatbotService->getConversations($perPage);

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting chatbot conversations', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las conversaciones',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }*/

    /**
     * @OA\Get(
     *     path="/api/developer-web/chatbot/analytics/summary",
     *     summary="Obtener analytics del chatbot (admin)",
     *     tags={"Chatbot Analytics"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Analytics obtenidos exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_conversations", type="integer", example=150),
     *                 @OA\Property(property="active_conversations", type="integer", example=5),
     *                 @OA\Property(property="avg_satisfaction", type="number", format="float", example=4.2),
     *                 @OA\Property(property="resolved_rate", type="number", format="float", example=0.85)
     *             )
     *         )
     *     )
     * )
     */
    public function getAnalytics(): JsonResponse
    {
        try {
            $conversationStats = $this->faqService->getConversationStats();

            return response()->json([
                'success' => true,
                'data' => $conversationStats
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting chatbot analytics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los analytics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}