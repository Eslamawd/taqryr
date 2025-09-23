<?php

use App\Http\Controllers\SubscripeController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
      // Subscription routes can be added here
      Route::post('/subscription/create', [SubscripeController::class, 'create']);
});
