<?php
// app/Domains/DeveloperWeb/Http/Controllers/Api/DashboardApiController.php

namespace App\Domains\DeveloperWeb\Http\Controllers\Api;

use App\Domains\DeveloperWeb\Services\NewsService;
use App\Domains\DeveloperWeb\Services\AnnouncementService;
use App\Domains\DeveloperWeb\Services\AlertService;
use App\Domains\DeveloperWeb\Services\ChatbotFaqService;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DashboardApiController
{
    public function __construct(
        private NewsService $newsService,
        private AnnouncementService $announcementService,
        private AlertService $alertService,
        private ChatbotFaqService $faqService,
        private ContactFormService $contactFormService
    ) {}

    
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = [
                'news' => $this->getNewsStats(),
                'announcements' => $this->getAnnouncementStats(),
                'alerts' => $this->getAlertStats(),
                'chatbot' => $this->getChatbotStats(),
                'contact_forms' => $this->getContactFormStats(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting dashboard statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas del dashboard',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    
    public function getWebStats(): JsonResponse
    {
        try {
            $stats = [
                'news' => $this->getNewsStats(),
                'announcements' => $this->getAnnouncementStats(),
                'alerts' => $this->getAlertStats(),
                'chatbot' => $this->getChatbotStats(),
                'contact_forms' => $this->getContactFormStats(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('API Error getting web stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas web',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function getNewsStats(): array
    {
        $statusCounts = $this->newsService->getStatusCounts();
        $totalViews = $this->newsService->getTotalViews();
        
        return [
            'total' => array_sum($statusCounts),
            'published' => $statusCounts['published'] ?? 0,
            'draft' => $statusCounts['draft'] ?? 0,
            'archived' => $statusCounts['archived'] ?? 0,
            'total_views' => $totalViews
        ];
    }

    private function getAnnouncementStats(): array
    {
        $statusCounts = $this->announcementService->getStatusCounts();
        $activeCount = $this->announcementService->getActiveCount();
        $totalViews = $this->announcementService->getTotalViews();
        
        return [
            'total' => array_sum($statusCounts),
            'published' => $activeCount,
            'total_views' => $totalViews
        ];
    }

    private function getAlertStats(): array
    {
        $statusCounts = $this->alertService->getStatusCounts();
        $activeCount = $this->alertService->getActiveCount();
        
        return [
            'total' => array_sum($statusCounts),
            'active' => $activeCount
        ];
    }

    private function getChatbotStats(): array
    {
        $totalFaqs = $this->faqService->getTotalFaqs();
        $activeFaqs = $this->faqService->getActiveFaqsCount();
        $conversationStats = $this->faqService->getConversationStats();
        
        return [
            'total_faqs' => $totalFaqs,
            'active_faqs' => $activeFaqs,
            'total_conversations' => $conversationStats['total'],
            'resolved_conversations' => $conversationStats['resolved']
        ];
    }

    private function getContactFormStats(): array
    {
        $stats = $this->contactFormService->getContactStats();
        
        return [
            'total' => array_sum($stats),
            'pending' => $stats['pending'] ?? 0,
            'in_progress' => $stats['in_progress'] ?? 0,
            'responded' => $stats['responded'] ?? 0
        ];
    }
}