<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar bindings para DeveloperWeb
        $this->app->bind(ContactFormRepository::class, function ($app) {
            return new ContactFormRepository();
        });

        $this->app->bind(ContactFormService::class, function ($app) {
            return new ContactFormService(
                $app->make(ContactFormRepository::class)
            );
        });
    }
    
    public function boot()
    {
        $modules = [
            'Administrator',
            'DataAnalyst',
            'DeveloperWeb',
            'LMS',
            'SupportInfrastructure',
            'SupportSecurity',
            'SupportTechnical',
            'AuthenticationSessions'
        ];

        foreach ($modules as $module) {
            $path = base_path("app/Domains/{$module}/routes.php");
            if (file_exists($path)) {
                require $path;
            }
        }
    }
}