<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MobilePaymentController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/m-payment', function () {
    return Inertia::render('Mpayment/Index');
})->middleware(['auth', 'verified'])->name('m-payment.index');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::prefix('m-payment')->name('m-payment.')->group(function () {
        // Route PLN
        Route::get('/pln', [MobilePaymentController::class, 'pln'])->name('pln');
        Route::post('/pln/purchase', [MobilePaymentController::class, 'purchasePln'])->name('pln.purchase');
        
        // Route PDAM (Air)
        Route::get('/air', [MobilePaymentController::class, 'air'])->name('air');
        Route::post('/air/purchase', [MobilePaymentController::class, 'purchaseAir'])->name('air.purchase');
        Route::post('/m-payment/air/purchase', [MobilepaymentController::class, 'purchaseAir'])->name('m-payment.air.purchase');
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';