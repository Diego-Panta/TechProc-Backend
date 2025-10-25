<?php

use Illuminate\Support\Facades\Route;
use App\Domains\SupportInfrastructure\Controllers\{
    SoftwareController,
    LicenseController,
    EmployeeController,
    DepartmentController
};

Route::prefix('infraestructura')->group(function () {
    Route::apiResource('softwares', SoftwareController::class);
    Route::apiResource('licenses', LicenseController::class);

    // Ruta especial para crear employee con usuario (debe ir ANTES del apiResource)
    Route::post('employees/create-with-user', [EmployeeController::class, 'createWithUser']);

    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', DepartmentController::class);
});