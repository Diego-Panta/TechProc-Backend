<?php
use Illuminate\Support\Facades\Route;


#Route::get('/infraestructura/licenses', [LicenseController::class, 'index']);
#Route::post('infraestructura/licenses', [LicenseController::class, '']);


Route::get('/hello', function() {
    return response() -> json(['message' => 'Hola desde Laravel Backend']);
});