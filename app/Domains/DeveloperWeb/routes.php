<?php

use App\Domains\DeveloperWeb\Http\Controllers\ContactFormController;
use App\Domains\DeveloperWeb\Http\Controllers\AnnouncementController;
use App\Domains\DeveloperWeb\Http\Controllers\AlertController;
use App\Domains\DeveloperWeb\Http\Controllers\NewsController;
use App\Domains\DeveloperWeb\Http\Controllers\ChatbotController;
use App\Domains\DeveloperWeb\Http\Controllers\ChatbotFaqController;
use Illuminate\Support\Facades\Route;

// Rutas públicas para noticias
Route::prefix('noticias')->name('public.news.')->group(function () {
    Route::get('/', [NewsController::class, 'publicIndex'])->name('index');
    Route::get('/{id}', [NewsController::class, 'publicShow'])->name('show');
    Route::get('/slug/{slug}', [NewsController::class, 'publicShowBySlug'])->name('show-by-slug');
});

// Rutas públicas para noticias (API)
Route::prefix('news')->name('public.news.api.')->group(function () {
    Route::get('/', [NewsController::class, 'apiIndex'])->name('index');
    Route::get('/{id}', [NewsController::class, 'apiShow'])->name('show');
    Route::get('/slug/{slug}', [NewsController::class, 'apiShowBySlug'])->name('show-by-slug');
});

// Rutas públicas para formulario de contacto
Route::prefix('contacto')->name('public.contact.')->group(function () {
    Route::get('/', [ContactFormController::class, 'create'])->name('create');
    Route::post('/', [ContactFormController::class, 'store'])->name('store');
});

// Rutas públicas para anuncios (API)
Route::prefix('announcements')->name('public.announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'apiIndex'])->name('api.index');
    Route::get('/{id}', [AnnouncementController::class, 'apiShow'])->name('api.show');
});

// Rutas públicas para anuncios
Route::prefix('anuncios')->name('public.announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'publicIndex'])->name('index');
    Route::get('/{id}', [AnnouncementController::class, 'publicShow'])->name('show');
});

// Rutas públicas para alertas (API)
Route::prefix('alerts')->name('public.alerts.')->group(function () {
    Route::get('/', [AlertController::class, 'apiIndex'])->name('api.index');
    Route::get('/high-priority', [AlertController::class, 'apiHighPriority'])->name('api.high-priority');
    Route::get('/{id}', [AlertController::class, 'apiShow'])->name('api.show');
});

// Rutas públicas para alertas
Route::prefix('alertas')->name('public.alerts.')->group(function () {
    Route::get('/', [AlertController::class, 'publicIndex'])->name('index');
    Route::get('/alta-prioridad', [AlertController::class, 'highPriority'])->name('high-priority');
});

// Rutas públicas para chatbot
Route::prefix('chatbot')->name('public.chatbot.')->group(function () {
    Route::get('/', [ChatbotController::class, 'publicChat'])->name('chat');
    Route::get('/faqs/category/{category?}', [ChatbotController::class, 'getFaqsByCategory'])->name('faqs.by-category');
});

// Rutas públicas para FAQs (API)
Route::prefix('chatbot-faqs')->name('public.chatbot.faqs.')->group(function () {
    Route::get('/', [ChatbotFaqController::class, 'apiIndex'])->name('api.index');
    Route::get('/{id}', [ChatbotFaqController::class, 'apiShow'])->name('api.show');
});

// Rutas del panel del desarrollador (requieren autenticación)
//Route::middleware(['auth'])->prefix('developer-web')->name('developer-web.')->group(function () {
Route::prefix('developer-web')->name('developer-web.')->group(function () {

    // News Routes
    Route::prefix('news')->name('news.')->group(function () {
        Route::get('/', [NewsController::class, 'index'])->name('index');
        Route::get('/create', [NewsController::class, 'create'])->name('create');
        Route::post('/', [NewsController::class, 'store'])->name('store');
        Route::get('/{id}', [NewsController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [NewsController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NewsController::class, 'update'])->name('update');
        Route::delete('/{id}', [NewsController::class, 'destroy'])->name('destroy');
        
        // Ruta para resetear vistas
        Route::post('/{id}/reset-views', [NewsController::class, 'resetViews'])->name('reset-views');
    });

    // Contact Forms
    Route::prefix('contact-forms')->name('contact-forms.')->group(function () {
        Route::get('/', [ContactFormController::class, 'index'])->name('index');
        Route::get('/{id}', [ContactFormController::class, 'show'])->name('show');
        Route::post('/{id}/spam', [ContactFormController::class, 'markAsSpam'])->name('mark-spam');
        Route::post('/{id}/respond', [ContactFormController::class, 'respond'])->name('respond');
        Route::post('/{id}/assign', [ContactFormController::class, 'assign'])->name('assign');
    });

    // Announcements
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/{id}', [AnnouncementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{id}', [AnnouncementController::class, 'destroy'])->name('destroy');

        // Ruta para resetear vistas (útil para testing)
        Route::post('/{id}/reset-views', [AnnouncementController::class, 'resetViews'])->name('reset-views');
    });

    // Alerts
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::get('/create', [AlertController::class, 'create'])->name('create');
        Route::post('/', [AlertController::class, 'store'])->name('store');
        Route::get('/{id}', [AlertController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AlertController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AlertController::class, 'update'])->name('update');
        Route::delete('/{id}', [AlertController::class, 'destroy'])->name('destroy');
    });

    // FAQs del Chatbot
    Route::prefix('chatbot/faqs')->name('chatbot.faqs.')->group(function () {
        Route::get('/', [ChatbotFaqController::class, 'index'])->name('index');
        Route::get('/create', [ChatbotFaqController::class, 'create'])->name('create');
        Route::post('/', [ChatbotFaqController::class, 'store'])->name('store');
        Route::get('/{id}', [ChatbotFaqController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ChatbotFaqController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ChatbotFaqController::class, 'update'])->name('update');
        Route::delete('/{id}', [ChatbotFaqController::class, 'destroy'])->name('destroy');
    });

    // APIs del Chatbot
    Route::prefix('chatbot')->name('chatbot.')->group(function () {
        Route::post('/conversation/start', [ChatbotController::class, 'startConversation'])->name('start-conversation');
        Route::post('/conversation/message', [ChatbotController::class, 'sendMessage'])->name('send-message');
        Route::post('/conversation/end', [ChatbotController::class, 'endConversation'])->name('end-conversation');
        Route::get('/faqs/category/{category?}', [ChatbotController::class, 'getFaqsByCategory'])->name('faqs.by-category');
    });
});

Route::get('/test-gemini', [ChatbotController::class, 'testGeminiConnection']);