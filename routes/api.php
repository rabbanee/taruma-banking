<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobilePaymentController;
use App\Http\Controllers\EWalletTransactionController;
use App\Http\Controllers\MyBalanceController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/m-payment', [MobilePaymentController::class, 'store']);
    Route::get('/m-payment/history', [MobilePaymentController::class, 'history']);
    Route::get('/m-payment/pln/history', [MobilePaymentController::class, 'plnHistory']);
    Route::get('/mybalance/{id}', [MyBalanceController::class, 'myBalance']);
    Route::get('/e-wallet/history', [EWalletTransactionController::class, 'history']);
    Route::get('/e-wallet/{id}', [EWalletTransactionController::class, 'show']);
    Route::get('/m-payment/air', [MobilePaymentController::class, 'airApi']);
    // Route::post('/lookup-account', [BankTransferController::class, 'lookupAccount'])->name('api.lookupAccount');


});

Route::middleware(['auth'])->group(function () {
    Route::post('/pln/purchase', [MobilePaymentController::class, 'purchasePln']);
});
;
