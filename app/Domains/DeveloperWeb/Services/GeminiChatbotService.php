<?php
// app/Domains/DeveloperWeb/Services/GeminiChatbotService.php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Repositories\ChatbotRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatbotService
{
    private $chatbotRepository;
    private $apiKey;
    private $model = 'gemini-2.0-flash-lite';

    public function __construct(ChatbotRepository $chatbotRepository)
    {
        $this->chatbotRepository = $chatbotRepository;
        $this->apiKey = config('services.gemini.api_key');

        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API Key no configurada');
        }
    }

    public function startConversation(): array
    {
        try {
            $config = app(ChatbotConfigService::class)->getConfig();

            // Verificar si el chatbot está habilitado
            if (!$config['enabled']) {
                throw new \Exception('El chatbot está desactivado temporalmente. Por favor, intente más tarde.');
            }

            Log::info('Creando nueva conversación...');

            $conversation = $this->chatbotRepository->createConversation([
                'started_at' => now(),
                'message_count' => 0,
            ]);

            Log::info('Conversación creada con ID: ' . $conversation->id);

            return [
                'success' => true,
                'conversation_id' => $conversation->id,
                'welcome_message' => $config['greeting_message'],
                'response_delay' => $config['response_delay']
            ];
        } catch (\Exception $e) {
            Log::error('Error al crear conversación: ' . $e->getMessage());
            throw new \Exception('No se pudo iniciar la conversación: ' . $e->getMessage());
        }
    }

    public function processMessage(string $message, int $conversationId): array
    {
        try {
            Log::info('Procesando mensaje para conversación: ' . $conversationId);

            $config = app(ChatbotConfigService::class)->getConfig();

            // Verificar si el chatbot está habilitado
            if (!$config['enabled']) {
                throw new \Exception('El chatbot está desactivado temporalmente. Por favor, intente más tarde.');
            }

            // Primero buscar en FAQs
            $faqMatch = $this->chatbotRepository->findMatchingFaq($message);

            if ($faqMatch) {
                Log::info('FAQ encontrada: ' . $faqMatch->id);
                $response = $this->handleFaqResponse($faqMatch, $conversationId, $message);
                $response['response_delay'] = $config['response_delay'];
                return $response;
            }

            Log::info('No se encontró FAQ, usando Gemini...');

            // Si no hay match, usar Gemini
            $geminiResponse = $this->callGeminiAPI($message);

            // Actualizar conversación con la respuesta
            $this->chatbotRepository->updateConversationWithMessage(
                $conversationId,
                $message,
                $geminiResponse['response']
            );

            return [
                'success' => true,
                'response' => $geminiResponse['response'],
                'source' => 'gemini',
                'conversation_id' => $conversationId,
                'response_delay' => $config['response_delay']
            ];
        } catch (\Exception $e) {
            Log::error('Error al procesar mensaje: ' . $e->getMessage());

            // Usar mensaje de fallback de la configuración
            $config = app(ChatbotConfigService::class)->getConfig();
            $fallbackResponse = $e->getMessage() === 'El chatbot está desactivado temporalmente. Por favor, intente más tarde.'
                ? $e->getMessage()
                : $config['fallback_message'];

            // Actualizar conversación con el error
            $this->chatbotRepository->updateConversationWithMessage(
                $conversationId,
                $message,
                $fallbackResponse
            );

            return [
                'success' => false,
                'response' => $fallbackResponse,
                'source' => 'fallback',
                'conversation_id' => $conversationId,
                'response_delay' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    private function handleFaqResponse($faq, int $conversationId, string $userMessage): array
    {
        try {
            // Incrementar contador de uso
            $this->chatbotRepository->incrementFaqUsage($faq->id);

            // Actualizar conversación con la respuesta de FAQ
            $this->chatbotRepository->updateConversationWithMessage(
                $conversationId,
                $userMessage,
                $faq->answer,
                $faq->id
            );

            return [
                'success' => true,
                'response' => $faq->answer,
                'source' => 'faq',
                'faq_id' => $faq->id,
                'conversation_id' => $conversationId,
            ];
        } catch (\Exception $e) {
            Log::error('Error handling FAQ response: ' . $e->getMessage());
            throw new \Exception('Error al procesar la respuesta de FAQ');
        }
    }

    private function callGeminiAPI(string $message): array
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}";

            Log::info('Llamando a Gemini API', [
                'url' => $url,
                'message_length' => strlen($message),
                'api_key_first_10' => substr($this->apiKey, 0, 10) . '...'
            ]);

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $this->buildPrompt($message)]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ]
            ];

            $response = Http::timeout(30)
                ->retry(3, 100)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            Log::info('Respuesta HTTP de Gemini:', [
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

                    Log::info('Texto generado por Gemini: ' . substr($generatedText, 0, 100) . '...');

                    return [
                        'response' => trim($generatedText),
                        'success' => true
                    ];
                } else {
                    Log::error('Estructura de respuesta inesperada de Gemini', ['data' => $data]);
                    throw new \Exception('Estructura de respuesta inesperada de la API');
                }
            }

            // Log del error detallado
            $errorBody = $response->body();
            Log::error('Error en Gemini API', [
                'status' => $response->status(),
                'body' => $errorBody
            ]);

            // Intentar parsear el error de Google
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message'] ?? $errorBody;

            throw new \Exception('Error en API: ' . $response->status() . ' - ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());

            return [
                'response' => 'Lo siento, estoy teniendo dificultades técnicas en este momento. Por favor, intenta nuevamente más tarde.',
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function buildPrompt(string $message): string
    {
        return "Eres un asistente virtual útil y amable para Incadev, una plataforma educativa. 
Responde en español de manera clara, concisa y profesional.
Si no sabes la respuesta, sugiere contactar con soporte humano.

Pregunta del usuario: {$message}

Respuesta:";
    }

    public function endConversation(int $conversationId, array $feedback = []): array
    {
        try {
            $updateData = [
                'ended_at' => now(),
            ];

            if (isset($feedback['rating'])) {
                $updateData['satisfaction_rating'] = $feedback['rating'];
            }

            if (isset($feedback['comment'])) {
                $updateData['feedback'] = $feedback['comment'];
            }

            if (isset($feedback['resolved'])) {
                $updateData['resolved'] = $feedback['resolved'];
            }

            $success = $this->chatbotRepository->updateConversation($conversationId, $updateData);

            return [
                'success' => $success,
                'message' => $success ? 'Conversación finalizada correctamente' : 'Error al finalizar la conversación'
            ];
        } catch (\Exception $e) {
            Log::error('Error al finalizar conversación: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al finalizar la conversación: ' . $e->getMessage()
            ];
        }
    }

    public function getConversationHistory(int $conversationId): ?array
    {
        try {
            $conversation = $this->chatbotRepository->getConversationWithMessages($conversationId);
            
            if (!$conversation) {
                return null;
            }

            return [
                'id' => $conversation->id,
                'started_at' => $conversation->started_at,
                'ended_at' => $conversation->ended_at,
                'first_message' => $conversation->first_message,
                'last_bot_response' => $conversation->last_bot_response,
                'message_count' => $conversation->message_count,
                'faq_matched' => $conversation->faqMatched,
                'satisfaction_rating' => $conversation->satisfaction_rating,
                'resolved' => $conversation->resolved,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting conversation history: ' . $e->getMessage());
            return null;
        }
    }
}