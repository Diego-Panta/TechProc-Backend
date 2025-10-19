<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\DeveloperWeb\Repositories\ContactFormRepository;
use App\Domains\DeveloperWeb\Services\ContactFormService;
use App\Domains\DataAnalyst\Repositories\StudentReportRepository;
use App\Domains\DataAnalyst\Services\StudentReportService;

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

        // Registrar bindings para DataAnalyst
        /*$this->app->bind(StudentReportRepository::class, function ($app) {
            return new StudentReportRepository();
        });

        $this->app->bind(StudentReportService::class, function ($app) {
            return new StudentReportService(
                $app->make(StudentReportRepository::class)
            );
        });*/
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
            // Cargar rutas web
            $webPath = base_path("app/Domains/{$module}/routes.php");
            if (file_exists($webPath)) {
                require $webPath;
            }

            // Cargar rutas API
            $apiPath = base_path("app/Domains/{$module}/api.php");
            if (file_exists($apiPath)) {
                require $apiPath;
            }
        }
    }
}