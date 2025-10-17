<?php

namespace App\Domains\AuthenticationSessions\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar modelos, repositorios, etc.
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar rutas del dominio
        $this->registerRoutes();
        
        // Registrar middleware
        $this->registerMiddleware();
    }

    /**
     * Register domain routes
     */
    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('app/Domains/AuthenticationSessions/routes.php'));
    }

    /**
     * Register domain middleware
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth', \App\Domains\AuthenticationSessions\Middleware\JwtMiddleware::class);
    }
}