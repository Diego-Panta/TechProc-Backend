<?php

namespace App\Domains\SupportInfrastructure\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Domains\SupportInfrastructure\Policies\TechAssetPolicy;
use App\Domains\SupportInfrastructure\Policies\HardwarePolicy;
use App\Domains\SupportInfrastructure\Policies\SoftwarePolicy;
use App\Domains\SupportInfrastructure\Policies\LicensePolicy;
use App\Domains\SupportInfrastructure\Policies\LicenseAssignmentPolicy;
use IncadevUns\CoreDomain\Models\TechAsset;
use IncadevUns\CoreDomain\Models\Hardware;
use IncadevUns\CoreDomain\Models\Software;
use IncadevUns\CoreDomain\Models\License;
use IncadevUns\CoreDomain\Models\LicenseAssignment;

class SupportInfrastructureServiceProvider extends ServiceProvider
{
    /**
     * Las policies del módulo de infraestructura
     */
    protected array $policies = [
        TechAsset::class => TechAssetPolicy::class,
        Hardware::class => HardwarePolicy::class,
        Software::class => SoftwarePolicy::class,
        License::class => LicensePolicy::class,
        LicenseAssignment::class => LicenseAssignmentPolicy::class,
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
            ->group(base_path('app/Domains/SupportInfrastructure/routes.php'));
    }
}
