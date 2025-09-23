<?php
use App\Http\Controllers\PlatformCatalogController;
use App\Http\Controllers\AdsController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function () {
      
    Route::post('/ads', [AdsController::class, 'store']);
    Route::get('/ads', [AdsController::class, 'index']);

});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/platform-catalog/sync', [PlatformCatalogController::class,'sync']);   // زر التحديث
    Route::get('/platform-catalog/options', [PlatformCatalogController::class,'options']); // للـ UI
});
