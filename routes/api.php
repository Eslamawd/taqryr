<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanSubscripeController;
use App\Http\Controllers\SubscripeController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
      
    Route::post('/payment/create', [PaymentController::class, 'create']);
    Route::post('/payment/banking', [PaymentController::class, 'banking']);
    Route::get('/payment/user', [PaymentController::class, 'getByUser']);
    Route::post('/subscriptions/{id}', [SubscripeController::class, 'store']);

});


      Route::get('/plan-subscripes', [PlanSubscripeController::class, 'index']);





require __DIR__.'/auth.php';
require __DIR__.'/subscriptions.php';
require __DIR__.'/ads.php';
require __DIR__.'/targeting.php';
require __DIR__.'/admin.php';
