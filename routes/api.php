<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobilePaymentController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/m-payment', [MobilePaymentController::class, 'store']);
    Route::get('/m-payment/history', [MobilePaymentController::class, 'history']);
    Route::get('/m-payment/pln/history', [MobilePaymentController::class, 'plnHistory']);


});

Route::middleware(['auth'])->group(function () {
    Route::post('/pln/purchase', [MobilePaymentController::class, 'purchasePln']);
});




