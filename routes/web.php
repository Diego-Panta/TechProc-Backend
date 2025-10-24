<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// RUTAS API
/*
require base_path('app/Domains/SupportSecurity/routes.php');
require base_path('app/Domains/Administrator/routes.php');
require base_path('App/Domains/SupportInfrastructure/routes.php');*/

