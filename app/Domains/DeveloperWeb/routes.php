<?php

use App\Domains\DeveloperWeb\Http\Controllers\ContactFormController;
use App\Domains\DeveloperWeb\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Route;

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

// Rutas del panel del desarrollador (requieren autenticación)
//Route::middleware(['auth'])->prefix('developer-web')->name('developer-web.')->group(function () {
Route::prefix('developer-web')->name('developer-web.')->group(function () {
    // Contact Forms
    Route::prefix('contact-forms')->name('contact-forms.')->group(function () {
        Route::get('/', [ContactFormController::class, 'index'])->name('index');
        Route::get('/{id}', [ContactFormController::class, 'show'])->name('show');
        Route::post('/{id}/spam', [ContactFormController::class, 'markAsSpam'])->name('mark-spam');
        Route::post('/{id}/respond', [ContactFormController::class, 'respond'])->name('respond');
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
});