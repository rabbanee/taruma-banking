<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobilePaymentController;
use App\Http\Controllers\MyBalanceController;

Route::post('/m-payment', [MobilePaymentController::class, 'store']);
Route::get('/m-payment/history', [MobilePaymentController::class, 'history']);
Route::get('/mybalance/{id}', [MyBalanceController::class, 'myBalance']);
