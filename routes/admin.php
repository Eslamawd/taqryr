<?php 

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdsCreateController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanSubscripeController;
use App\Http\Controllers\SubscripeController;
use App\Http\Controllers\WalletController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware([AdminMiddleware::class])->prefix('admin')->group(function () {
        //get All Users Change Role 
         Route::get('users', [AdminController::class, 'index']);
         Route::delete('users', [AdminController::class, 'destroy']);
         Route::post('users/{id}/change-role', [AdminController::class, 'changeRole']);

         
        Route::post('/wallet/deposit/{id}', [WalletController::class, 'deposit']);
        Route::post('/wallet/withdraw/{id}', [WalletController::class, 'withdraw']);
        
         //Plan Subscription
         Route::apiResource('plan-subscripes', PlanSubscripeController::class);
         //Ads get And Created or Deleted
         Route::post('ads/{id}', [AdsCreateController::class, 'store']);
         Route::get('ads/', [AdsCreateController::class, 'index']);
         
         Route::get('payments', [PaymentController::class, 'index']);
         Route::put('payments/{id}/status', [PaymentController::class, 'updateStatus']);

         //Subscription
            Route::get('subscriptions', [SubscripeController::class, 'index']);
            Route::get('subscriptions/count', [SubscripeController::class, 'count']);
            Route::get('subscriptions/revenue', [SubscripeController::class, 'getRevenue']);

    });
});