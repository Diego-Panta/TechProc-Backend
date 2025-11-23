<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\JWTServiceProvider::class,
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    App\Providers\DomainServiceProvider::class,
    App\Domains\Security\Providers\SecurityServiceProvider::class,
    App\Domains\SupportInfrastructure\Providers\SupportInfrastructureServiceProvider::class,
];
