<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $modules = [
            'Administrator',
            'DataAnalyst',
            'DeveloperWeb',
            'LMS',
            'SupportInfrastructure',
            'SupportSecurity',
            'Shared'
        ];

        foreach ($modules as $module) {
            $path = base_path("app/Domains/{$module}/routes.php");
            if (file_exists($path)) {
                require $path;
            }
        }
    }
}