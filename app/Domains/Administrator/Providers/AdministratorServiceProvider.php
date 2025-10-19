<?php

namespace App\Domains\Administrator\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Administrator\Services\AdminService;
use App\Domains\Administrator\Middleware\AdminMiddleware;

class AdministratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el servicio AdminService
        $this->app->singleton(AdminService::class, function ($app) {
            return new AdminService();
        });

        // Registrar el middleware AdminMiddleware
        $this->app->singleton(AdminMiddleware::class, function ($app) {
            return new AdminMiddleware($app->make(\App\Domains\AuthenticationSessions\Services\JwtService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar rutas del dominio
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');

        // Registrar vistas si las hay
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'administrator');

        // Registrar migraciones si las hay
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Registrar traducciones si las hay
        // $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'administrator');

        // Publicar archivos de configuración
        // $this->publishes([
        //     __DIR__ . '/../config/administrator.php' => config_path('administrator.php'),
        // ], 'config');

        // Publicar vistas
        // $this->publishes([
        //     __DIR__ . '/../resources/views' => resource_path('views/vendor/administrator'),
        // ], 'views');

        // Publicar assets
        // $this->publishes([
        //     __DIR__ . '/../resources/assets' => public_path('vendor/administrator'),
        // ], 'public');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AdminService::class,
            AdminMiddleware::class,
        ];
    }
}
