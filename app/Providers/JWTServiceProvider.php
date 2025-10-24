<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\App;

class JWTServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar alias manualmente
        App::bind('JWTAuth', function() {
            return JWTAuth::class;
        });
    }
}