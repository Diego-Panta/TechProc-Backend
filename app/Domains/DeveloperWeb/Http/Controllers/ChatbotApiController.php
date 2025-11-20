<?php
// app/Domains/DeveloperWeb/Http/Controllers/Api/ChatbotApiController.php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Services\GeminiChatbotService;
use App\Domains\DeveloperWeb\Services\ChatbotFaqService;
use App\Domains\DeveloperWeb\Http\Requests\ChatbotMessageApiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotApiController
{
    public function __construct(
        private GeminiChatbotService $chatbotService,
        private ChatbotFaqService $faqService
    ) {}

    public function startConversation(): JsonResponse
    {
        try {
            $result = $this->chatbotService->startConversation();

            return response()->json([
                'success' => $result['success'],
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('API Error starting chatbot conversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar la conversación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function sendMessage(ChatbotMessageApiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $response = $this->chatbotService->processMessage(
                $validated['message'],
                $validated['conversation_id']
            );

            return response()->json([
                'success' => $response['success'],
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('API Error sending chatbot message', [
                'conversation_id' => $request->conversation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el mensaje',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function endConversation(ChatbotMessageApiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $result = $this->chatbotService->endConversation(
                $validated['conversation_id'],
                $request->input('feedback', [])
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            Log::error('API Error ending chatbot conversation', [
                'conversation_id' => $request->conversation_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la conversación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preguntas frecuentes',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getConversationHistory(int $conversationId): JsonResponse
    {
        try {
            $history = $this->chatbotService->getConversationHistory($conversationId);

            if (!$history) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversación no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting conversation history', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de conversación',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los analytics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}