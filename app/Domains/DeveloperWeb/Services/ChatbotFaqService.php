<?php
// app/Domains/DeveloperWeb/Services/ChatbotFaqService.php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Repositories\ChatbotRepository;
use Incadev\Core\Models\ChatbotFaq;
use App\Domains\DeveloperWeb\Enums\FaqCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ChatbotFaqService
{
    public function __construct(
        private ChatbotRepository $chatbotRepository
    ) {}

    public function getAllFaqs(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            return $this->chatbotRepository->getAllFaqsPaginated($perPage, $filters);
        } catch (\Exception $e) {
            Log::error('Error in getAllFaqs service method', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getFaqById(int $id): ?ChatbotFaq
    {
        try {
            return $this->chatbotRepository->findFaqById($id);
        } catch (\Exception $e) {
            Log::error('Error in getFaqById service method', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function createFaq(array $data): ChatbotFaq
    {
        try {
            $data = $this->validateAndNormalizeCategory($data);
            $cleanData = $this->prepareFaqData($data);
            
            return $this->chatbotRepository->createFaq($cleanData);
        } catch (\Exception $e) {
            Log::error('Error in createFaq service method', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateFaq(int $id, array $data): bool
    {
        try {
            $faq = $this->chatbotRepository->findFaqById($id);

            if (!$faq) {
                throw new \Exception("FAQ no encontrada con ID: {$id}");
            }

            $data = $this->validateAndNormalizeCategory($data);
            $cleanData = $this->prepareFaqData($data);
            
            $cleanData = array_filter($cleanData, function($value) {
                return !is_null($value);
            });

            return $this->chatbotRepository->updateFaq($faq, $cleanData);
        } catch (\Exception $e) {
            Log::error('Error in updateFaq service method', [
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deleteFaq(int $id): bool
    {
        try {
            $faq = $this->chatbotRepository->findFaqById($id);

            if (!$faq) {
                throw new \Exception("FAQ no encontrada con ID: {$id}");
            }

            return $this->chatbotRepository->deleteFaq($faq);
        } catch (\Exception $e) {
            Log::error('Error in deleteFaq service method', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getCategories(): array
    {
        try {
            return $this->chatbotRepository->getCategories();
        } catch (\Exception $e) {
            Log::error('Error in getCategories service method', [
                'error' => $e->getMessage()
            ]);
            return FaqCategory::values(); // Fallback
        }
    }

    public function getCategoriesWithLabels(): array
    {
        try {
            return $this->chatbotRepository->getCategoriesWithLabels();
        } catch (\Exception $e) {
            Log::error('Error in getCategoriesWithLabels service method', [
                'error' => $e->getMessage()
            ]);
            return FaqCategory::labels(); // Fallback
        }
    }

    public function getFaqsForPublic(array $filters = []): array
    {
        try {
            $filters['active'] = true;
            return $this->chatbotRepository->getAllFaqsPaginated(50, $filters)->items();
        } catch (\Exception $e) {
            Log::error('Error in getFaqsForPublic service method', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            return []; // En caso de error, retornar array vacío
        }
    }

    /**
     * Validar y normalizar la categoría
     */
    private function validateAndNormalizeCategory(array $data): array
    {
        if (isset($data['category'])) {
            $category = $data['category'];
            
            if (!FaqCategory::isValid($category)) {
                $data['category'] = FaqCategory::getDefault()->value;
            }
        } else {
            $data['category'] = FaqCategory::getDefault()->value;
        }

        return $data;
    }

    /**
     * Preparar y limpiar datos de FAQ antes de guardar
     */
    private function prepareFaqData(array $data): array
    {
        if (isset($data['keywords'])) {
            if (is_string($data['keywords'])) {
                try {
                    $keywords = json_decode($data['keywords'], true);
                    $data['keywords'] = (json_last_error() === JSON_ERROR_NONE) ? $keywords : [];
                } catch (\Exception $e) {
                    $data['keywords'] = [];
                }
            }

            if (is_array($data['keywords'])) {
                $data['keywords'] = array_filter($data['keywords'], function ($keyword) {
                    return !empty(trim($keyword));
                });
                $data['keywords'] = array_values($data['keywords']);
            } else {
                $data['keywords'] = [];
            }
        } else {
            unset($data['keywords']);
        }

        if (isset($data['active'])) {
            $data['active'] = (bool)$data['active'];
        } else {
            unset($data['active']);
        }

        if (array_key_exists('category', $data) && is_null($data['category'])) {
            unset($data['category']);
        }

        return $data;
    }

    public function getTotalFaqs(): int
    {
        try {
            return $this->chatbotRepository->getTotalFaqs();
        } catch (\Exception $e) {
            Log::error('Error in getTotalFaqs service method', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function getActiveFaqsCount(): int
    {
        try {
            return $this->chatbotRepository->getActiveFaqsCount();
        } catch (\Exception $e) {
            Log::error('Error in getActiveFaqsCount service method', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function getConversationStats(): array
    {
        try {
            return $this->chatbotRepository->getConversationStats();
        } catch (\Exception $e) {
            Log::error('Error in getConversationStats service method', [
                'error' => $e->getMessage()
            ]);
            return [
                'total' => 0,
                'resolved' => 0,
                'active' => 0,
                'resolved_rate' => 0,
                'avg_satisfaction' => 0,
            ];
        }
    }
}