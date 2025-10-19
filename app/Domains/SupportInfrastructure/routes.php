<?php
use Illuminate\Support\Facades\Route;
#use App\Domains\SupportInfrastructure\Controllers\{
    #SoftwareController,
    #LicenseController,
    #EmployeeController
#};


use App\Domains\SupportInfrastructure\Controllers\{
    SoftwareController,
    LicenseController,
    EmployeeController
};

Route::prefix('api/infraestructura')->group(function () { 
    Route::apiResource('softwares',SoftwareController::class);
    Route::apiResource('licenses',LicenseController::class);
    Route::apiResource('employees',EmployeeController::class);
});