<?php
// app/Domains/DeveloperWeb/Services/ChatbotFaqService.php

namespace App\Domains\DeveloperWeb\Services;

use App\Domains\DeveloperWeb\Repositories\ChatbotRepository;
use App\Domains\DeveloperWeb\Models\ChatbotFaq;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatbotFaqService
{
    public function __construct(
        private ChatbotRepository $chatbotRepository
    ) {}

    public function getAllFaqs(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->chatbotRepository->getAllFaqsPaginated($perPage, $filters);
    }

    public function getFaqById(int $id): ?ChatbotFaq
    {
        return $this->chatbotRepository->findFaqById($id);
    }

    public function createFaq(array $data): ChatbotFaq
    {
        // Limpiar y preparar datos
        $cleanData = $this->prepareFaqData($data);
        return $this->chatbotRepository->createFaq($cleanData);
    }

    public function updateFaq(int $id, array $data): bool
    {
        $faq = $this->chatbotRepository->findFaqById($id);

        if (!$faq) {
            return false;
        }

        // Limpiar y preparar datos
        $cleanData = $this->prepareFaqData($data);
        
        // Remover campos null para no sobreescribir con null
        $cleanData = array_filter($cleanData, function($value) {
            return !is_null($value);
        });

        return $this->chatbotRepository->updateFaq($faq, $cleanData);
    }

    public function deleteFaq(int $id): bool
    {
        $faq = $this->chatbotRepository->findFaqById($id);

        if (!$faq) {
            return false;
        }

        return $this->chatbotRepository->deleteFaq($faq);
    }

    public function getCategories(): array
    {
        return $this->chatbotRepository->getCategories();
    }

    public function getFaqsForPublic(array $filters = []): array
    {
        $filters['active'] = true;
        return $this->chatbotRepository->getAllFaqsPaginated(50, $filters)->items();
    }

    /**
     * Preparar y limpiar datos de FAQ antes de guardar
     */
    private function prepareFaqData(array $data): array
    {
        // Asegurarse de que keywords sea un array válido
        if (isset($data['keywords'])) {
            if (is_string($data['keywords'])) {
                try {
                    $keywords = json_decode($data['keywords'], true);
                    $data['keywords'] = (json_last_error() === JSON_ERROR_NONE) ? $keywords : [];
                } catch (\Exception $e) {
                    $data['keywords'] = [];
                }
            }

            // Filtrar keywords vacías
            if (is_array($data['keywords'])) {
                $data['keywords'] = array_filter($data['keywords'], function ($keyword) {
                    return !empty(trim($keyword));
                });
                $data['keywords'] = array_values($data['keywords']); // Reindexar
            } else {
                $data['keywords'] = [];
            }
        } else {
            // Si no se proporciona keywords, mantener el valor existente
            unset($data['keywords']);
        }

        // Asegurarse de que active sea boolean si se proporciona
        if (isset($data['active'])) {
            $data['active'] = (bool)$data['active'];
        } else {
            // Si no se proporciona active, mantener el valor existente
            unset($data['active']);
        }

        // Si category es null, mantener la categoría existente
        if (array_key_exists('category', $data) && is_null($data['category'])) {
            unset($data['category']);
        }

        return $data;
    }

    public function getTotalFaqs(): int
    {
        return $this->chatbotRepository->getTotalFaqs();
    }

    public function getActiveFaqsCount(): int
    {
        return $this->chatbotRepository->getActiveFaqsCount();
    }

    public function getConversationStats(): array
    {
        return $this->chatbotRepository->getConversationStats();
    }
}
