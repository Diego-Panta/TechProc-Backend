<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require base_path('app/Domains/Lms/routes.php');
require base_path('app/Domains/DataAnalyst/routes.php');
require base_path('app/Domains/DeveloperWeb/routes.php');
require base_path('app/Domains/SupportSecurity/routes.php');
require base_path('app/Domains/Administrator/routes.php');
require base_path('App/Domains/SupportInfrastructure/routes.php');

