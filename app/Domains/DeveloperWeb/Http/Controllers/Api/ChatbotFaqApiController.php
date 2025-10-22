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


    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->faqService->getCategoriesWithLabels();

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
