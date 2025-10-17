<?php
// app/Domains/DeveloperWeb/Repositories/ChatbotRepository.php

namespace App\Domains\DeveloperWeb\Repositories;

use App\Domains\DeveloperWeb\Models\ChatbotConversation;
use App\Domains\DeveloperWeb\Models\ChatbotMessage;
use App\Domains\DeveloperWeb\Models\ChatbotFaq;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ChatbotRepository
{
    public function getAllFaqsPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = ChatbotFaq::query();

        // Aplicar filtros
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('question', 'ILIKE', "%{$search}%")
                    ->orWhere('answer', 'ILIKE', "%{$search}%")
                    ->orWhere('category', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('usage_count', 'desc')
            ->orderBy('created_date', 'desc')
            ->paginate($perPage);
    }

    public function findFaqById(int $id): ?ChatbotFaq
    {
        return ChatbotFaq::find($id);
    }

    public function createFaq(array $data): ChatbotFaq
    {
        $data['id_faq'] = $this->getNextFaqId();
        return ChatbotFaq::create($data);
    }

    public function updateFaq(ChatbotFaq $faq, array $data): bool
    {
        $data['updated_date'] = now();
        return $faq->update($data);
    }

    public function deleteFaq(ChatbotFaq $faq): bool
    {
        try {
            DB::beginTransaction();

            // Paso 1: Establecer faq_matched a NULL en todos los mensajes que referencian esta FAQ
            ChatbotMessage::where('faq_matched', $faq->id)
                ->update(['faq_matched' => null]);

            // Paso 2: Eliminar la FAQ
            $deleted = $faq->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function incrementFaqUsage(int $faqId): bool
    {
        return ChatbotFaq::where('id', $faqId)->increment('usage_count');
    }

    public function getNextFaqId(): int
    {
        $lastFaq = ChatbotFaq::orderBy('id_faq', 'desc')->first();
        return $lastFaq ? $lastFaq->id_faq + 1 : 1;
    }

    public function getCategories(): array
    {
        return ChatbotFaq::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public function getActiveFaqs()
    {
        return ChatbotFaq::where('active', true)->get();
    }

    public function findMatchingFaq(string $question): ?ChatbotFaq
    {
        $faqs = $this->getActiveFaqs();

        foreach ($faqs as $faq) {
            $similarity = $this->calculateSimilarity($question, $faq->question);

            if ($similarity > 0.6) { // 60% de similitud
                return $faq;
            }
        }

        return null;
    }

    private function calculateSimilarity(string $text1, string $text2): float
    {
        $words1 = array_count_values(str_word_count(mb_strtolower($text1), 1));
        $words2 = array_count_values(str_word_count(mb_strtolower($text2), 1));

        $intersection = array_intersect_key($words1, $words2);
        $dotProduct = array_sum($intersection);

        $norm1 = sqrt(array_sum(array_map(function ($x) {
            return $x * $x;
        }, $words1)));
        $norm2 = sqrt(array_sum(array_map(function ($x) {
            return $x * $x;
        }, $words2)));

        if ($norm1 * $norm2 == 0) return 0;

        return $dotProduct / ($norm1 * $norm2);
    }

    // Métodos para conversaciones
    public function createConversation(array $data): ChatbotConversation
    {
        // Para bigint, usar un número entero en lugar de string
        $data['id_conversation'] = $this->getNextConversationId();
        return ChatbotConversation::create($data);
    }

    public function addMessage(array $data): ChatbotMessage
    {
        // Para bigint, usar un número entero en lugar de string
        $data['id_message'] = $this->getNextMessageId();
        return ChatbotMessage::create($data);
    }

    public function getConversationWithMessages(int $conversationId)
    {
        return ChatbotConversation::with('messages.faq')->find($conversationId);
    }

    public function updateConversation(int $conversationId, array $data): bool
    {
        return ChatbotConversation::where('id', $conversationId)->update($data);
    }

    private function getNextConversationId(): int
    {
        $lastConversation = ChatbotConversation::orderBy('id_conversation', 'desc')->first();
        return $lastConversation ? $lastConversation->id_conversation + 1 : 1;
    }

    private function getNextMessageId(): int
    {
        $lastMessage = ChatbotMessage::orderBy('id_message', 'desc')->first();
        return $lastMessage ? $lastMessage->id_message + 1 : 1;
    }

    public function getTotalFaqs(): int
    {
        return ChatbotFaq::count();
    }

    public function getActiveFaqsCount(): int
    {
        return ChatbotFaq::where('active', true)->count();
    }

    public function getConversationStats(): array
    {
        $total = ChatbotConversation::count();
        $resolved = ChatbotConversation::where('resolved', true)->count();

        return [
            'total' => $total,
            'resolved' => $resolved
        ];
    }

    /*public function getTotalFaqs(): int
    {
        return ChatbotFaq::whereNull('deleted_at')->count();
    }

    public function getActiveFaqsCount(): int
    {
        return ChatbotFaq::where('active', true)
            ->whereNull('deleted_at')
            ->count();
    }

    public function getConversationStats(): array
    {
        $totalConversations = ChatbotConversation::count();
        $activeConversations = ChatbotConversation::whereNull('ended_date')->count();
        $resolvedConversations = ChatbotConversation::where('resolved', true)->count();

        $avgSatisfaction = ChatbotConversation::whereNotNull('satisfaction_rating')
            ->avg('satisfaction_rating');

        return [
            'total_conversations' => $totalConversations,
            'active_conversations' => $activeConversations,
            'avg_satisfaction' => $avgSatisfaction ? round($avgSatisfaction, 2) : 0,
            'resolved_rate' => $totalConversations > 0 ? round($resolvedConversations / $totalConversations, 2) : 0,
        ];
    }*/
}
