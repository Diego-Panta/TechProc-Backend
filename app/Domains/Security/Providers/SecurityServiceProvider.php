<?php

namespace App\Domains\Security\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar rutas
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('app/Domains/Security/routes.php'));
    }
}
