<?php

namespace App\Http\Controllers;

use IncadevUns\CoreDomain\Models\ChatbotFaq;
use IncadevUns\CoreDomain\Models\ChatbotConversation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    // ============ FAQs ============

    public function indexFaqs(Request $request): JsonResponse
    {
        $query = ChatbotFaq::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $faqs = $query->orderBy('usage_count', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }

    public function storeFaq(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string',
            'active' => 'boolean'
        ]);

        $faq = ChatbotFaq::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ creada exitosamente',
            'data' => $faq
        ], 201);
    }

    public function showFaq(ChatbotFaq $faq): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $faq
        ]);
    }

    public function updateFaq(Request $request, ChatbotFaq $faq): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'sometimes|required|string|max:500',
            'answer' => 'sometimes|required|string',
            'category' => 'nullable|string|max:100',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string',
            'active' => 'boolean'
        ]);

        $faq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ actualizada exitosamente',
            'data' => $faq
        ]);
    }

    public function destroyFaq(ChatbotFaq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ eliminada exitosamente'
        ]);
    }

    public function searchFaqs(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:3'
        ]);

        $query = $request->input('query');

        $faqs = ChatbotFaq::where('active', true)
            ->where(function ($q) use ($query) {
                $q->where('question', 'like', "%{$query}%")
                    ->orWhere('answer', 'like', "%{$query}%")
                    ->orWhereJsonContains('keywords', $query);
            })
            ->orderBy('usage_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }

    public function mostUsedFaqs(): JsonResponse
    {
        $faqs = ChatbotFaq::where('active', true)
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }

    // ============ CONVERSACIONES ============

    public function indexConversations(Request $request): JsonResponse
    {
        $query = ChatbotConversation::with('faqMatched');

        if ($request->has('resolved')) {
            $query->where('resolved', $request->boolean('resolved'));
        }

        if ($request->has('handed_to_human')) {
            $query->where('handed_to_human', $request->boolean('handed_to_human'));
        }

        $conversations = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    public function storeConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_message' => 'required|string|max:1000'
        ]);

        $conversation = ChatbotConversation::create([
            'started_at' => now(),
            'first_message' => $validated['first_message'],
            'message_count' => 1,
            'resolved' => false,
            'handed_to_human' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación iniciada',
            'data' => $conversation
        ], 201);
    }

    public function showConversation(ChatbotConversation $conversation): JsonResponse
    {
        $conversation->load('faqMatched');

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    public function resolveConversation(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'resolved' => 'required|boolean',
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $conversation->update([
            'resolved' => $validated['resolved'],
            'satisfaction_rating' => $validated['satisfaction_rating'] ?? null,
            'feedback' => $validated['feedback'] ?? null,
            'ended_at' => $validated['resolved'] ? now() : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación actualizada',
            'data' => $conversation
        ]);
    }

    public function handToHuman(Request $request, ChatbotConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'handed_to_human' => 'required|boolean',
            'reason' => 'nullable|string|max:500'
        ]);

        $conversation->update([
            'handed_to_human' => $validated['handed_to_human']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación transferida a soporte humano',
            'data' => $conversation
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total_conversations' => ChatbotConversation::count(),
            'resolved_conversations' => ChatbotConversation::where('resolved', true)->count(),
            'handed_to_human' => ChatbotConversation::where('handed_to_human', true)->count(),
            'average_satisfaction' => ChatbotConversation::whereNotNull('satisfaction_rating')
                ->avg('satisfaction_rating'),
            'total_messages' => ChatbotConversation::sum('message_count'),
            'most_matched_faqs' => ChatbotFaq::where('usage_count', '>', 0)
                ->orderBy('usage_count', 'desc')
                ->limit(5)
                ->get(['id', 'question', 'usage_count'])
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
