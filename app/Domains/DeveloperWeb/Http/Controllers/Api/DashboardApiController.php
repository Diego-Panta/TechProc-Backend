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

    /**
     * @OA\Get(
     *     path="/api/developer-web/dashboard/statistics",
     *     summary="Obtener estadísticas completas del dashboard",
     *     tags={"Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="news", type="object",
     *                     @OA\Property(property="total", type="integer", example=92),
     *                     @OA\Property(property="published", type="integer", example=78),
     *                     @OA\Property(property="draft", type="integer", example=12),
     *                     @OA\Property(property="archived", type="integer", example=2),
     *                     @OA\Property(property="total_views", type="integer", example=45678)
     *                 ),
     *                 @OA\Property(property="announcements", type="object",
     *                     @OA\Property(property="total", type="integer", example=12),
     *                     @OA\Property(property="active", type="integer", example=5),
     *                     @OA\Property(property="total_views", type="integer", example=12345)
     *                 ),
     *                 @OA\Property(property="alerts", type="object",
     *                     @OA\Property(property="total", type="integer", example=8),
     *                     @OA\Property(property="active", type="integer", example=3)
     *                 ),
     *                 @OA\Property(property="chatbot", type="object",
     *                     @OA\Property(property="total_faqs", type="integer", example=45),
     *                     @OA\Property(property="active_faqs", type="integer", example=42),
     *                     @OA\Property(property="total_conversations", type="integer", example=567),
     *                     @OA\Property(property="resolved_conversations", type="integer", example=489)
     *                 ),
     *                 @OA\Property(property="contact_forms", type="object",
     *                     @OA\Property(property="total", type="integer", example=234),
     *                     @OA\Property(property="pending", type="integer", example=23),
     *                     @OA\Property(property="in_progress", type="integer", example=45),
     *                     @OA\Property(property="responded", type="integer", example=166)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/developer-web/dashboard/web-stats",
     *     summary="Obtener estadísticas web para frontend desacoplado",
     *     tags={"Dashboard"},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas web obtenidas exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="news", type="object",
     *                     @OA\Property(property="total", type="integer", example=92),
     *                     @OA\Property(property="published", type="integer", example=78),
     *                     @OA\Property(property="draft", type="integer", example=12),
     *                     @OA\Property(property="archived", type="integer", example=2),
     *                     @OA\Property(property="total_views", type="integer", example=45678)
     *                 ),
     *                 @OA\Property(property="announcements", type="object",
     *                     @OA\Property(property="total", type="integer", example=12),
     *                     @OA\Property(property="active", type="integer", example=5),
     *                     @OA\Property(property="total_views", type="integer", example=12345)
     *                 ),
     *                 @OA\Property(property="alerts", type="object",
     *                     @OA\Property(property="total", type="integer", example=8),
     *                     @OA\Property(property="active", type="integer", example=3)
     *                 ),
     *                 @OA\Property(property="chatbot", type="object",
     *                     @OA\Property(property="total_faqs", type="integer", example=45),
     *                     @OA\Property(property="active_faqs", type="integer", example=42),
     *                     @OA\Property(property="total_conversations", type="integer", example=567),
     *                     @OA\Property(property="resolved_conversations", type="integer", example=489)
     *                 ),
     *                 @OA\Property(property="contact_forms", type="object",
     *                     @OA\Property(property="total", type="integer", example=234),
     *                     @OA\Property(property="pending", type="integer", example=23),
     *                     @OA\Property(property="in_progress", type="integer", example=45),
     *                     @OA\Property(property="responded", type="integer", example=166)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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
            'active' => $activeCount,
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