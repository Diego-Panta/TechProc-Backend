<?php
use Illuminate\Support\Facades\Route;
#use App\Domains\SupportInfrastructure\Controllers\{
    #SoftwareController,
    #LicenseController,
    #EmployeeController
#};


use App\Domains\SupportSecurity\Controllers\{
    ActiveSessionController,
    BlockedIpController,
    IncidentController,
    SecurityAlertController,
    SecurityConfigurationController,
    SecurityLogController,
};

Route::prefix('api/seguridad')->group(function () { 
    Route::apiResource('activeSession',ActiveSessionController::class);
    Route::apiResource('blockedIp',BlockedIpController::class);
    Route::apiResource('incident',IncidentController::class);
    Route::apiResource('securityAlert',SecurityAlertController::class);
    Route::apiResource('securityConfiguration',SecurityConfigurationController::class);
    Route::apiResource('securityLog',SecurityLogController::class);

});
