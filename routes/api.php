<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobilePaymentController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/m-payment', [MobilePaymentController::class, 'store']);
    Route::get('/m-payment/history', [MobilePaymentController::class, 'history']);
    Route::get('/m-payment/pln/history', [MobilePaymentController::class, 'plnHistory']);
    Route::get('/m-payment/air', [MobilePaymentController::class, 'airApi']);
    Route::post('/lookup-account', [BankTransferController::class, 'lookupAccount'])
         ->name('api.lookupAccount');


});

Route::middleware(['auth'])->group(function () {
    Route::post('/pln/purchase', [MobilePaymentController::class, 'purchasePln']);
});



