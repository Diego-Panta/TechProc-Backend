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
                'started_date' => now(),
            ]);

            Log::info('Conversación creada con ID: ' . $conversation->id);

            return [
                'conversation_id' => $conversation->id,
                'welcome_message' => $config['greeting_message'] // Usar mensaje configurado
            ];
        } catch (\Exception $e) {
            Log::error('Error al crear conversación: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            throw new \Exception('No se pudo iniciar la conversación: ' . $e->getMessage());
        }
    }

    public function processMessage(string $message, int $conversationId): array
    {
        try {
            Log::info('Procesando mensaje para conversación: ' . $conversationId);

            $config = app(ChatbotConfigService::class)->getConfig();

            //  Verificar si el chatbot está habilitado
            if (!$config['enabled']) {
                throw new \Exception('El chatbot está desactivado temporalmente. Por favor, intente más tarde.');
            }

            // Guardar mensaje del usuario
            $userMessage = $this->chatbotRepository->addMessage([
                'conversation_id' => $conversationId,
                'sender' => 'user',
                'message' => $message,
            ]);

            Log::info('Mensaje del usuario guardado: ' . $userMessage->id);

            // Primero buscar en FAQs
            $faqMatch = $this->chatbotRepository->findMatchingFaq($message);

            if ($faqMatch) {
                Log::info('FAQ encontrada: ' . $faqMatch->id);
                $response = $this->handleFaqResponse($faqMatch, $conversationId);
                $response['response_delay'] = $config['response_delay'];
                return $response;
            }

            Log::info('No se encontró FAQ, usando Gemini...');

            // Si no hay match, usar Gemini
            $geminiResponse = $this->callGeminiAPI($message);

            // Guardar respuesta del bot
            $botMessage = $this->chatbotRepository->addMessage([
                'conversation_id' => $conversationId,
                'sender' => 'bot',
                'message' => $geminiResponse['response'],
            ]);

            Log::info('Respuesta del bot guardada: ' . $botMessage->id);

            return [
                'response' => $geminiResponse['response'],
                'source' => 'gemini',
                'conversation_id' => $conversationId,
                'response_delay' => $config['response_delay'] // Frontend maneja el delay
            ];
        } catch (\Exception $e) {
            Log::error('Error al procesar mensaje: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            // Usar mensaje de fallback de la configuración
            $config = app(ChatbotConfigService::class)->getConfig();
            $fallbackResponse = $e->getMessage() === 'El chatbot está desactivado temporalmente. Por favor, intente más tarde.'
                ? $e->getMessage()
                : $config['fallback_message'];

            $this->chatbotRepository->addMessage([
                'conversation_id' => $conversationId,
                'sender' => 'bot',
                'message' => $fallbackResponse,
            ]);

            return [
                'response' => $fallbackResponse,
                'source' => 'fallback',
                'conversation_id' => $conversationId,
                'response_delay' => 0 // Sin delay en errores
            ];
        }
    }

    private function handleFaqResponse($faq, int $conversationId): array
    {
        // Incrementar contador de uso
        $this->chatbotRepository->incrementFaqUsage($faq->id);

        // Guardar respuesta del bot con referencia al FAQ
        $this->chatbotRepository->addMessage([
            'conversation_id' => $conversationId,
            'sender' => 'bot',
            'message' => $faq->answer,
            'faq_matched' => $faq->id,
        ]);

        return [
            'response' => $faq->answer,
            'source' => 'faq',
            'faq_id' => $faq->id,
            'conversation_id' => $conversationId,
        ];
    }

    private function callGeminiAPI(string $message): array
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}";

            Log::info('Llamando a Gemini API', [
                'url' => $url,
                'message' => $message,
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

            Log::debug('Payload enviado a Gemini:', $payload);

            $response = Http::timeout(30)
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

                Log::debug('Respuesta completa de Gemini:', $data);

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

                    Log::info('Texto generado por Gemini: ' . $generatedText);

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
                'body' => $errorBody,
                'headers' => $response->headers()
            ]);

            // Intentar parsear el error de Google
            $errorData = json_decode($errorBody, true);
            $errorMessage = $errorData['error']['message'] ?? $errorBody;

            throw new \Exception('Error en API: ' . $response->status() . ' - ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return [
                'response' => 'Lo siento, estoy teniendo dificultades técnicas en este momento. Por favor, intenta nuevamente más tarde.',
                'success' => false
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

    public function endConversation(int $conversationId, array $feedback = []): bool
    {
        try {
            return $this->chatbotRepository->updateConversation($conversationId, [
                'ended_date' => now(),
                'satisfaction_rating' => $feedback['rating'] ?? null,
                'feedback' => $feedback['comment'] ?? null,
                'resolved' => $feedback['resolved'] ?? true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al finalizar conversación: ' . $e->getMessage());
            return false;
        }
    }
}
