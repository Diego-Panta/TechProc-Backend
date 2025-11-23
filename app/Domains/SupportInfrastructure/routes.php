<?php

use Illuminate\Support\Facades\Route;
use App\Domains\SupportInfrastructure\Http\Controllers\{
    SoftwareController,
    HardwareController,
    LicenseController,
    TechAssetController,
    LicenseAssignmentController
};

Route::prefix('infrastructure')->middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('softwares', SoftwareController::class);
    Route::apiResource('licenses', LicenseController::class);
    Route::apiResource('hardwares', HardwareController::class);
    Route::apiResource('assets', TechAssetController::class);
    Route::apiResource('assignments', LicenseAssignmentController::class);
});