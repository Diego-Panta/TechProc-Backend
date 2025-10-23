<?php

use App\Domains\DeveloperWeb\Http\Controllers\Api\ContactFormApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\AnnouncementApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\AlertApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\NewsApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\ChatbotFaqApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\ChatbotApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\DashboardApiController;
use App\Domains\DeveloperWeb\Http\Controllers\Api\ChatbotConfigController;

use App\Domains\DeveloperWeb\Middleware\DeveloperWebMiddleware;
use Illuminate\Support\Facades\Route;

// API Routes for DeveloperWeb module
Route::prefix('developer-web')->name('api.developer-web.')->group(function () {

    // Dashboard Statistics API
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Public endpoints (sin autenticación)
        Route::get('/web-stats', [DashboardApiController::class, 'getWebStats'])->name('web-stats');

        // Protected endpoints (requieren autenticación y rol developer web)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {
            Route::get('/statistics', [DashboardApiController::class, 'getStatistics'])->name('statistics');
        });
    });

    // Contact Forms API
    Route::prefix('contact-forms')->name('contact-forms.')->group(function () {
        // Public endpoint to submit contact forms (sin autenticación)
        Route::post('/', [ContactFormApiController::class, 'store'])->name('store');

        // Protected endpoints (requieren autenticación y rol web)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {

            Route::get('/status-options', [ContactFormApiController::class, 'getStatusOptions'])->name('status-options');
            Route::get('/stats/enhanced', [ContactFormApiController::class, 'getEnhancedStats'])->name('stats.enhanced');
            
            Route::get('/', [ContactFormApiController::class, 'index'])->name('index');
            Route::get('/{id}', [ContactFormApiController::class, 'show'])->name('show');
            Route::post('/{id}/spam', [ContactFormApiController::class, 'markAsSpam'])->name('mark-spam');
            Route::post('/{id}/respond', [ContactFormApiController::class, 'respond'])->name('respond');
            Route::get('/stats/summary', [ContactFormApiController::class, 'getStats'])->name('stats');
            

            // Nuevos endpoints para gestión avanzada
            Route::put('/{id}/assign', [ContactFormApiController::class, 'assignToMe'])->name('assign');
            Route::put('/{id}/status', [ContactFormApiController::class, 'updateStatus'])->name('update-status');
            //Route::get('/export/csv', [ContactFormApiController::class, 'exportToCsv'])->name('export.csv');
        });
    });


    // Announcements API
    Route::prefix('announcements')->name('announcements.')->group(function () {
        // Public endpoints (sin autenticación)
        Route::get('/public', [AnnouncementApiController::class, 'publicIndex'])->name('public.index');
        Route::get('/public/{id}', [AnnouncementApiController::class, 'publicShow'])->name('public.show');

        // Protected endpoints (requieren autenticación y rol developer web)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {
            Route::get('/', [AnnouncementApiController::class, 'index'])->name('index');
            Route::get('/{id}', [AnnouncementApiController::class, 'show'])->name('show');
            Route::post('/', [AnnouncementApiController::class, 'store'])->name('store');
            Route::put('/{id}', [AnnouncementApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [AnnouncementApiController::class, 'destroy'])->name('destroy');
            Route::get('/stats/summary', [AnnouncementApiController::class, 'getStats'])->name('stats');
            Route::post('/{id}/reset-views', [AnnouncementApiController::class, 'resetViews'])->name('reset-views');
        });
    });


    // Alerts API
    Route::prefix('alerts')->name('alerts.')->group(function () {
        // Public endpoints (sin autenticación)
        Route::get('/public', [AlertApiController::class, 'publicIndex'])->name('public.index');
        Route::get('/public/high-priority', [AlertApiController::class, 'publicHighPriority'])->name('public.high-priority');
        Route::get('/public/{id}', [AlertApiController::class, 'publicShow'])->name('public.show');

        // Protected endpoints (requieren autenticación y rol developer web)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {
            Route::get('/', [AlertApiController::class, 'index'])->name('index');
            Route::get('/{id}', [AlertApiController::class, 'show'])->name('show');
            Route::post('/', [AlertApiController::class, 'store'])->name('store');
            Route::put('/{id}', [AlertApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [AlertApiController::class, 'destroy'])->name('destroy');
            Route::get('/stats/summary', [AlertApiController::class, 'getStats'])->name('stats');
        });
    });

    // News API
    Route::prefix('news')->name('news.')->group(function () {
        // Public endpoints (sin autenticación)
        Route::get('/public', [NewsApiController::class, 'publicIndex'])->name('public.index');
        Route::get('/public/categories', [NewsApiController::class, 'getCategories'])->name('public.categories');
        Route::get('/public/{id}', [NewsApiController::class, 'publicShow'])->name('public.show');
        Route::get('/public/slug/{slug}', [NewsApiController::class, 'publicShowBySlug'])->name('public.show-by-slug');

        // Protected endpoints (requieren autenticación y rol developer web)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {
            Route::get('/', [NewsApiController::class, 'index'])->name('index');
            Route::get('/{id}', [NewsApiController::class, 'show'])->name('show');
            Route::post('/', [NewsApiController::class, 'store'])->name('store');
            Route::put('/{id}', [NewsApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [NewsApiController::class, 'destroy'])->name('destroy');
            Route::get('/stats/summary', [NewsApiController::class, 'getStats'])->name('stats');
            Route::post('/{id}/reset-views', [NewsApiController::class, 'resetViews'])->name('reset-views');
            Route::get('/{id}/related', [NewsApiController::class, 'getRelatedNews'])->name('related');
        });
    });

    // Chatbot FAQs API
    Route::prefix('chatbot/faqs')->name('chatbot.faqs.')->group(function () {
        // Public endpoints
        Route::get('/public', [ChatbotFaqApiController::class, 'publicIndex'])->name('public.index');
        Route::get('/public/{id}', [ChatbotFaqApiController::class, 'publicShow'])->name('public.show');
        Route::get('/categories', [ChatbotFaqApiController::class, 'getCategories'])->name('categories');

        // Protected endpoints (requieren autenticación)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {
            Route::get('/', [ChatbotFaqApiController::class, 'index'])->name('index');
            Route::get('/{id}', [ChatbotFaqApiController::class, 'show'])->name('show');
            Route::post('/', [ChatbotFaqApiController::class, 'store'])->name('store');
            Route::put('/{id}', [ChatbotFaqApiController::class, 'update'])->name('update');
            Route::delete('/{id}', [ChatbotFaqApiController::class, 'destroy'])->name('destroy');
            Route::get('/stats/summary', [ChatbotFaqApiController::class, 'getStats'])->name('stats');
        });
    });

    // Chatbot Conversations API
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        // Public endpoints
        Route::post('/conversation/start', [ChatbotApiController::class, 'startConversation'])->name('conversation.start');
        Route::post('/conversation/message', [ChatbotApiController::class, 'sendMessage'])->name('conversation.message');
        Route::post('/conversation/end', [ChatbotApiController::class, 'endConversation'])->name('conversation.end');
        Route::get('/categories/faqs/{category?}', [ChatbotApiController::class, 'getFaqsByCategory'])->name('faqs.by-category');

        // Protected endpoints para analytics (requieren autenticación)
        Route::middleware([DeveloperWebMiddleware::class])->group(function () {
            Route::get('/config', [ChatbotConfigController::class, 'getConfig'])->name('config.get');
            Route::put('/config', [ChatbotConfigController::class, 'updateConfig'])->name('config.update');
            Route::post('/config/reset', [ChatbotConfigController::class, 'resetConfig'])->name('config.reset');
            Route::get('/config/health', [ChatbotConfigController::class, 'healthCheck'])->name('config.health');
            Route::get('/conversations', [ChatbotApiController::class, 'getConversations'])->name('conversations.index');
            Route::get('/conversations/{id}', [ChatbotApiController::class, 'getConversation'])->name('conversations.show');
            Route::get('/analytics/summary', [ChatbotApiController::class, 'getAnalytics'])->name('analytics.summary');
        });
    });
});
