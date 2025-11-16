<?php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Http\Requests\StoreContactFormApiRequest;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactFormApiController
{
    public function __construct(
        private ContactFormService $contactFormService
    ) {}

    /**
     * Recibir formulario de contacto (PÚBLICO)
     */
    public function store(StoreContactFormApiRequest $request): JsonResponse
    {
        try {
            $result = $this->contactFormService->processContactForm($request->validated());

            return response()->json($result, $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            Log::error('API Error processing contact form', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el formulario de contacto',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Responder a formulario de contacto (PROTEGIDO)
     */
    public function respond(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'contact_data' => 'required|array',
                'contact_data.email' => 'required|email',
                'contact_data.full_name' => 'required|string',
                'contact_data.subject' => 'required|string',
                'response' => 'required|string|min:10'
            ]);

            $result = $this->contactFormService->respondToContact(
                $validated['contact_data'], 
                $validated['response']
            );

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('API Error responding to contact', [
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la respuesta',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}