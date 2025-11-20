<?php

use Illuminate\Support\Facades\Route;
use App\Domains\SupportInfrastructure\Http\Controllers\{
    SoftwareController,
    HardwareController,
    LicenseController, 
    TechAssetController,
    LicenseAssignmentController
};

Route::prefix('infrastructure')->group(function () {
    Route::apiResource('softwares', SoftwareController::class);
    Route::apiResource('licenses', LicenseController::class);
    Route::apiResource('hardwares', HardwareController::class);
    Route::apiResource('assets', TechAssetController::class);
    Route::apiResource('assignments', LicenseAssignmentController::class);
    // Ruta especial para crear employee con usuario (debe ir ANTES del apiResource)
    #Route::post('employees/create-with-user', [EmployeeController::class, 'createWithUser']);
    Route::apiResource('assets', TechAssetController::class);
});