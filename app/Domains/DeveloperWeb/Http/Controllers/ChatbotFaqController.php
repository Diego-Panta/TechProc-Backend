<?php
// app/Domains/DeveloperWeb/Http/Controllers/ChatbotFaqController.php

namespace App\Domains\DeveloperWeb\Http\Controllers;

use App\Domains\DeveloperWeb\Services\ChatbotFaqService;
use App\Domains\DeveloperWeb\Http\Requests\StoreFaqRequest;
use App\Domains\DeveloperWeb\Http\Requests\UpdateFaqRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ChatbotFaqController
{
    public function __construct(
        private ChatbotFaqService $faqService
    ) {}

    // Panel admin - Listar FAQs
    public function index(): View
    {
        $filters = [
            'category' => request('category'),
            'active' => request('active'),
            'search' => request('search'),
        ];

        $faqs = $this->faqService->getAllFaqs(15, $filters);
        $categories = $this->faqService->getCategories();

        return view('developer-web.chatbot.faqs.index', compact(
            'faqs',
            'categories',
            'filters'
        ));
    }

    // Mostrar formulario de creación
    public function create(): View
    {
        $categories = $this->faqService->getCategories();
        return view('developer-web.chatbot.faqs.create', compact('categories'));
    }

    // Almacenar nueva FAQ
    public function store(StoreFaqRequest $request): RedirectResponse
    {
        try {
            $faq = $this->faqService->createFaq($request->validated());

            return redirect()->route('developer-web.chatbot.faqs.index')
                ->with('success', 'FAQ creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear FAQ', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la FAQ: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Mostrar detalles de FAQ
    public function show(int $id): View
    {
        $faq = $this->faqService->getFaqById($id);

        if (!$faq) {
            abort(404);
        }

        return view('developer-web.chatbot.faqs.show', compact('faq'));
    }

    // Mostrar formulario de edición
    public function edit(int $id): View
    {
        $faq = $this->faqService->getFaqById($id);
        $categories = $this->faqService->getCategories();

        if (!$faq) {
            abort(404);
        }

        return view('developer-web.chatbot.faqs.edit', compact('faq', 'categories'));
    }

    // Actualizar FAQ
    public function update(UpdateFaqRequest $request, int $id): RedirectResponse
    {
        try {
            $success = $this->faqService->updateFaq($id, $request->validated());

            if ($success) {
                return redirect()->route('developer-web.chatbot.faqs.index')
                    ->with('success', 'FAQ actualizada exitosamente.');
            }

            return redirect()->route('developer-web.chatbot.faqs.index')
                ->with('error', 'No se pudo actualizar la FAQ.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar FAQ', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar la FAQ: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Eliminar FAQ
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
            Log::error('Error al eliminar FAQ', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la FAQ'
            ], 500);
        }
    }

    // API para frontend - Listar FAQs activas
    public function apiIndex(): JsonResponse
    {
        try {
            $filters = [
                'category' => request('category'),
                'search' => request('search'),
            ];

            $faqs = $this->faqService->getFaqsForPublic($filters);

            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            Log::error('Error en API de FAQs', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las FAQs'
            ], 500);
        }
    }

    // API para frontend - Mostrar FAQ específica
    public function apiShow(int $id): JsonResponse
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
            Log::error('Error en API de FAQ específica', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la FAQ'
            ], 500);
        }
    }
}