<?php
// app/Domains/DeveloperWeb/Http/Controllers/ChatbotController.php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Services\GeminiChatbotService;
use App\Domains\DeveloperWeb\Services\ChatbotFaqService;
use App\Domains\DeveloperWeb\Http\Requests\ChatbotMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ChatbotController
{
    public function __construct(
        private GeminiChatbotService $chatbotService,
        private ChatbotFaqService $faqService
    ) {}

    // Vista pública del chatbot
    public function publicChat(): View
    {
        $categories = $this->faqService->getCategories();
        $faqs = $this->faqService->getFaqsForPublic();
        
        return view('developer-web.chatbot.public-chat', compact('categories', 'faqs'));
    }

    // API - Obtener FAQs por categoría
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
            Log::error('Error al obtener FAQs por categoría: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las preguntas frecuentes'
            ], 500);
        }
    }

    // API - Iniciar conversación
    public function startConversation(): JsonResponse
    {
        try {
            $conversation = $this->chatbotService->startConversation();

            return response()->json([
                'success' => true,
                'data' => $conversation
            ]);
        } catch (\Exception $e) {
            Log::error('Error en startConversation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar conversación: ' . $e->getMessage()
            ], 500);
        }
    }

    // API - Enviar mensaje
    public function sendMessage(ChatbotMessageRequest $request): JsonResponse
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
            Log::error('Error en sendMessage: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    // API - Finalizar conversación
    public function endConversation(ChatbotMessageRequest $request): JsonResponse
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
            Log::error('Error en endConversation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar conversación: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método temporal para probar la conexión con Gemini
    public function testGeminiConnection(): JsonResponse
    {
        try {
            Log::info('=== INICIANDO PRUEBA GEMINI ===');

            // Verificar configuración
            $apiKey = config('services.gemini.api_key');
            Log::info('API Key configurada: ' . (!empty($apiKey) ? 'SÍ' : 'NO'));

            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'error' => 'API Key no configurada',
                    'config' => config('services.gemini')
                ], 500);
            }

            // Probar crear conversación
            Log::info('Intentando crear conversación...');
            $conversation = $this->chatbotService->startConversation();
            Log::info('Conversación creada: ' . json_encode($conversation));

            // Probar mensaje simple
            $testMessage = "Hola, responde con 'OK' si estás funcionando.";
            Log::info('Enviando mensaje: ' . $testMessage);

            $response = $this->chatbotService->processMessage($testMessage, $conversation['conversation_id']);
            Log::info('Respuesta recibida: ' . json_encode($response));

            return response()->json([
                'success' => true,
                'conversation' => $conversation,
                'response' => $response,
                'api_key_length' => strlen($apiKey)
            ]);
        } catch (\Exception $e) {
            Log::error('ERROR EN PRUEBA GEMINI: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'api_key_set' => !empty(config('services.gemini.api_key'))
            ], 500);
        }
    }
}
