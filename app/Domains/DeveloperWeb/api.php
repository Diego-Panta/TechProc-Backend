<?php

use App\Domains\DeveloperWeb\Http\Controllers\ContentTypes\AnnouncementApiController;
use App\Domains\DeveloperWeb\Http\Controllers\ContentTypes\AlertApiController;
use App\Domains\DeveloperWeb\Http\Controllers\ContentTypes\NewsApiController;
use App\Domains\DeveloperWeb\Http\Controllers\ChatbotFaqApiController;
use App\Domains\DeveloperWeb\Http\Controllers\ChatbotApiController;
use App\Domains\DeveloperWeb\Http\Controllers\ChatbotConfigController;
use App\Domains\DeveloperWeb\Http\Controllers\ContentStatsController;
use App\Domains\DeveloperWeb\Http\Controllers\ContactFormApiController;
use App\Domains\DeveloperWeb\Http\Controllers\LandingPageController;

use Illuminate\Support\Facades\Route;

// API Routes for DeveloperWeb module
Route::prefix('developer-web')->name('api.developer-web.')->group(function () {

    // ESTADÍSTICAS GENERALES
    Route::prefix('stats')->name('stats.')->group(function () {
        Route::get('/overall', [ContentStatsController::class, 'getOverallStats'])->name('overall');
    });

    // NEWS
    Route::prefix('news')->name('news.')->group(function () {
        // CRUD básico
        Route::get('/', [NewsApiController::class, 'index'])->name('index');
        Route::get('/{id}', [NewsApiController::class, 'show'])->name('show');
        Route::post('/', [NewsApiController::class, 'store'])->name('store');
        Route::put('/{id}', [NewsApiController::class, 'update'])->name('update');
        Route::delete('/{id}', [NewsApiController::class, 'destroy'])->name('destroy');
        
        // Métodos específicos SOLICITADOS - CAMBIAR NOMBRE
        Route::get('/list/published', [NewsApiController::class, 'getPublished'])->name('published');
        Route::post('/{id}/reset-views', [NewsApiController::class, 'resetViews'])->name('reset-views');
        Route::get('/list/categories', [NewsApiController::class, 'getCategories'])->name('categories');
        Route::get('/stats/summary', [NewsApiController::class, 'getStats'])->name('stats');
    });

    // ANNOUNCEMENTS
    Route::prefix('announcements')->name('announcements.')->group(function () {
        // CRUD básico
        Route::get('/', [AnnouncementApiController::class, 'index'])->name('index');
        Route::get('/{id}', [AnnouncementApiController::class, 'show'])->name('show');
        Route::post('/', [AnnouncementApiController::class, 'store'])->name('store');
        Route::put('/{id}', [AnnouncementApiController::class, 'update'])->name('update');
        Route::delete('/{id}', [AnnouncementApiController::class, 'destroy'])->name('destroy');
        
        // Métodos específicos SOLICITADOS - CAMBIAR NOMBRE
        Route::get('/list/published', [AnnouncementApiController::class, 'getPublished'])->name('published');
        Route::post('/{id}/reset-views', [AnnouncementApiController::class, 'resetViews'])->name('reset-views');
        Route::get('/stats/summary', [AnnouncementApiController::class, 'getStats'])->name('stats');
    });

    // ALERTS
    Route::prefix('alerts')->name('alerts.')->group(function () {
        // CRUD básico
        Route::get('/', [AlertApiController::class, 'index'])->name('index');
        Route::get('/{id}', [AlertApiController::class, 'show'])->name('show');
        Route::post('/', [AlertApiController::class, 'store'])->name('store');
        Route::put('/{id}', [AlertApiController::class, 'update'])->name('update');
        Route::delete('/{id}', [AlertApiController::class, 'destroy'])->name('destroy');
        
        // Métodos específicos SOLICITADOS - CAMBIAR NOMBRE
        Route::get('/list/published', [AlertApiController::class, 'getPublished'])->name('published');
        Route::get('/stats/summary', [AlertApiController::class, 'getStats'])->name('stats');
    });

    Route::prefix('contact-forms')->name('contact-forms.')->group(function () {
        // Public endpoint to submit contact forms
        Route::post('/', [ContactFormApiController::class, 'store'])->name('store');
    });

    // Chatbot FAQs API
    Route::prefix('chatbot/faqs')->name('chatbot.faqs.')->group(function () {
        // Public endpoints
        Route::get('/public', [ChatbotFaqApiController::class, 'publicIndex'])->name('public.index');
        Route::get('/public/{id}', [ChatbotFaqApiController::class, 'publicShow'])->name('public.show');
        Route::get('/categories', [ChatbotFaqApiController::class, 'getCategories'])->name('categories');

        // Protected endpoints (requieren autenticación)
        // Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ChatbotFaqApiController::class, 'index'])->name('index');
        Route::get('/{id}', [ChatbotFaqApiController::class, 'show'])->name('show');
        Route::post('/', [ChatbotFaqApiController::class, 'store'])->name('store');
        Route::put('/{id}', [ChatbotFaqApiController::class, 'update'])->name('update');
        Route::delete('/{id}', [ChatbotFaqApiController::class, 'destroy'])->name('destroy');
        Route::get('/stats/summary', [ChatbotFaqApiController::class, 'getStats'])->name('stats');
        // });
    });

    // Chatbot Conversations API
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        // Public endpoints
        Route::post('/conversation/start', [ChatbotApiController::class, 'startConversation'])->name('conversation.start');
        Route::post('/conversation/message', [ChatbotApiController::class, 'sendMessage'])->name('conversation.message');
        Route::post('/conversation/end', [ChatbotApiController::class, 'endConversation'])->name('conversation.end');
        //Route::get('/faqs/category/{category?}', [ChatbotApiController::class, 'getFaqsByCategory'])->name('faqs.by-category');

        // Protected endpoints para analytics (requieren autenticación)
        // Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/conversations/{id}', [ChatbotApiController::class, 'getConversationHistory'])->name('conversations.show');
        Route::get('/analytics/summary', [ChatbotApiController::class, 'getAnalytics'])->name('analytics.summary');
        // });
    });

    // Chatbot Configuration API
    Route::prefix('chatbot/config')->name('chatbot.config.')->group(function () {
        // Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ChatbotConfigController::class, 'getConfig'])->name('get');
        Route::put('/', [ChatbotConfigController::class, 'updateConfig'])->name('update');
        Route::post('/reset', [ChatbotConfigController::class, 'resetConfig'])->name('reset');
        Route::get('/health', [ChatbotConfigController::class, 'healthCheck'])->name('health');
        // });
    });

    // ========================================
    // LANDING PAGE API - PUBLIC ENDPOINTS
    // ========================================
    Route::prefix('landing')->name('landing.')->group(function () {
        // 1. Hero Section - Estadísticas
        Route::get('/hero-stats', [LandingPageController::class, 'getHeroStats'])->name('hero-stats');

        // 2. Cursos disponibles
        Route::get('/courses', [LandingPageController::class, 'getAvailableCourses'])->name('courses');

        // 3. Profesores destacados
        Route::get('/featured-teachers', [LandingPageController::class, 'getFeaturedTeachers'])->name('featured-teachers');

        // 4. Testimonios de estudiantes
        Route::get('/testimonials', [LandingPageController::class, 'getTestimonials'])->name('testimonials');

        // 5. Noticias públicas
        Route::get('/news', [LandingPageController::class, 'getPublicNews'])->name('news');
        Route::get('/news/{slug}', [LandingPageController::class, 'getNewsDetail'])->name('news.detail');

        // 6. Anuncios activos
        Route::get('/announcements', [LandingPageController::class, 'getActiveAnnouncements'])->name('announcements');

        // 7. Alertas activas
        Route::get('/alerts', [LandingPageController::class, 'getActiveAlerts'])->name('alerts');
    });
});
