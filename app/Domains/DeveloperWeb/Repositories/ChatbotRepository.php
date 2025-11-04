<?php
// app/Domains/DeveloperWeb/Repositories/ChatbotRepository.php

namespace App\Domains\DeveloperWeb\Repositories;

use Incadev\Core\Models\ChatbotConversation;
use Incadev\Core\Models\ChatbotFaq;
use App\Domains\DeveloperWeb\Enums\FaqCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatbotRepository
{
    public function getAllFaqsPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = ChatbotFaq::query();

        // Aplicar filtros
        if (!empty($filters['category'])) {
            // Validar que la categoría sea válida
            if (FaqCategory::isValid($filters['category'])) {
                $query->where('category', $filters['category']);
            }
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
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findFaqById(int $id): ?ChatbotFaq
    {
        return ChatbotFaq::find($id);
    }

    public function createFaq(array $data): ChatbotFaq
    {
        try {
            return ChatbotFaq::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating FAQ: ' . $e->getMessage());
            throw new \Exception('No se pudo crear la FAQ: ' . $e->getMessage());
        }
    }

    public function updateFaq(ChatbotFaq $faq, array $data): bool
    {
        try {
            return $faq->update($data);
        } catch (\Exception $e) {
            Log::error('Error updating FAQ: ' . $e->getMessage());
            throw new \Exception('No se pudo actualizar la FAQ: ' . $e->getMessage());
        }
    }

    public function deleteFaq(ChatbotFaq $faq): bool
    {
        try {
            DB::beginTransaction();

            // Paso 1: Establecer faq_matched_id a NULL en todas las conversaciones que referencian esta FAQ
            ChatbotConversation::where('faq_matched_id', $faq->id)
                ->update(['faq_matched_id' => null]);

            // Paso 2: Eliminar la FAQ
            $deleted = $faq->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting FAQ: ' . $e->getMessage());
            throw new \Exception('No se pudo eliminar la FAQ: ' . $e->getMessage());
        }
    }

    public function incrementFaqUsage(int $faqId): bool
    {
        try {
            return ChatbotFaq::where('id', $faqId)->increment('usage_count');
        } catch (\Exception $e) {
            Log::error('Error incrementing FAQ usage: ' . $e->getMessage());
            return false;
        }
    }

    public function getCategories(): array
    {
        return FaqCategory::values();
    }

    public function getCategoriesWithLabels(): array
    {
        return FaqCategory::labels();
    }

    public function getActiveFaqs()
    {
        return ChatbotFaq::where('active', true)->get();
    }

    public function findMatchingFaq(string $question): ?ChatbotFaq
    {
        try {
            $faqs = $this->getActiveFaqs();

            foreach ($faqs as $faq) {
                $similarity = $this->calculateSimilarity($question, $faq->question);

                if ($similarity > 0.6) { // 60% de similitud
                    return $faq;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error finding matching FAQ: ' . $e->getMessage());
            return null;
        }
    }

    private function calculateSimilarity(string $text1, string $text2): float
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error calculating similarity: ' . $e->getMessage());
            return 0;
        }
    }

    // Métodos para conversaciones
    public function createConversation(array $data): ChatbotConversation
    {
        try {
            return ChatbotConversation::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating conversation: ' . $e->getMessage());
            throw new \Exception('No se pudo crear la conversación: ' . $e->getMessage());
        }
    }

    public function getConversationWithMessages(int $conversationId)
    {
        return ChatbotConversation::with('faqMatched')->find($conversationId);
    }

    public function updateConversation(int $conversationId, array $data): bool
    {
        try {
            $conversation = ChatbotConversation::find($conversationId);
            if (!$conversation) {
                throw new \Exception('Conversación no encontrada');
            }
            return $conversation->update($data);
        } catch (\Exception $e) {
            Log::error('Error updating conversation: ' . $e->getMessage());
            throw new \Exception('No se pudo actualizar la conversación: ' . $e->getMessage());
        }
    }

    public function updateConversationWithMessage(int $conversationId, string $userMessage, string $botResponse, ?int $faqMatchedId = null): bool
    {
        try {
            $conversation = ChatbotConversation::find($conversationId);
            if (!$conversation) {
                throw new \Exception('Conversación no encontrada');
            }

            $updateData = [
                'last_bot_response' => $botResponse,
                'message_count' => $conversation->message_count + 1
            ];

            // Si es el primer mensaje, guardarlo
            if ($conversation->message_count === 0) {
                $updateData['first_message'] = $userMessage;
            }

            // Si hay FAQ match, actualizar
            if ($faqMatchedId) {
                $updateData['faq_matched_id'] = $faqMatchedId;
            }

            return $conversation->update($updateData);
        } catch (\Exception $e) {
            Log::error('Error updating conversation with message: ' . $e->getMessage());
            return false;
        }
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
        try {
            $total = ChatbotConversation::count();
            $resolved = ChatbotConversation::where('resolved', true)->count();
            $active = ChatbotConversation::whereNull('ended_at')->count();
            $avgSatisfaction = ChatbotConversation::whereNotNull('satisfaction_rating')
                ->avg('satisfaction_rating');

            return [
                'total' => $total,
                'resolved' => $resolved,
                'active' => $active,
                'resolved_rate' => $total > 0 ? round($resolved / $total, 2) : 0,
                'avg_satisfaction' => $avgSatisfaction ? round($avgSatisfaction, 2) : 0,
                'handed_to_human' => ChatbotConversation::where('handed_to_human', true)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting conversation stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'resolved' => 0,
                'active' => 0,
                'resolved_rate' => 0,
                'avg_satisfaction' => 0,
                'handed_to_human' => 0,
            ];
        }
    }
}