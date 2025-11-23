<?php

namespace App\Domains\Security\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Domains\Security\Models\UserSession;
use App\Domains\Security\Models\SecuritySetting;
use App\Domains\Security\Policies\SecurityEventPolicy;
use App\Domains\Security\Policies\SessionPolicy;
use App\Domains\Security\Policies\UserBlockPolicy;
use App\Domains\Security\Policies\SecuritySettingPolicy;
use IncadevUns\CoreDomain\Models\SecurityEvent;
use IncadevUns\CoreDomain\Models\UserBlock;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Las policies del módulo de seguridad
     */
    protected array $policies = [
        SecurityEvent::class => SecurityEventPolicy::class,
        UserSession::class => SessionPolicy::class,
        UserBlock::class => UserBlockPolicy::class,
        SecuritySetting::class => SecuritySettingPolicy::class,
    ];

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
        // Registrar policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Registrar rutas
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('app/Domains/Security/routes.php'));
    }
}
