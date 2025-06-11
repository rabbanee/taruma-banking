<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobilePaymentController;

Route::post('/m-payment', [MobilePaymentController::class, 'store']);
Route::get('/m-payment/history', [MobilePaymentController::class, 'history']);
