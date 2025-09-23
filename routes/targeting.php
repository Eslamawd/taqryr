<?php

use App\Http\Controllers\TargetingController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
      
    Route::get('/targeting', [TargetingController::class, 'index']);
    Route::get('/country', [TargetingController::class, 'getCountry']);
    
});
