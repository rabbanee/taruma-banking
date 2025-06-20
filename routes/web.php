<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MobilePaymentController;
use App\Http\Controllers\PaymentController;
use App\Models\EWalletTransaction;
use App\Http\Controllers\EWalletTransactionController;



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

        // Route E-Wallet
        Route::get('/ewallet', function () {
        return Inertia::render('Mpayment/Ewallet');
        })->name('ewallet');


    });

});

Route::prefix('mtransfer')->group(function () {
    Route::get('/', [MobilePaymentController::class, 'menu'])->name('mtransfer.menu');
    Route::get('/daftar-rekening', [MobilePaymentController::class, 'daftarRekening'])->name('mtransfer.daftar');
    Route::get('/transfer', [MobilePaymentController::class, 'transfer'])->name('mtransfer.transfer');
    Route::get('/recipients', [MobilePaymentController::class, 'getRecipients']);
     Route::get('/history', [MobilePaymentController::class, 'transactionHistory']);
     Route::post('/recipients', [MobilePaymentController::class, 'storeRecipient']);
    Route::post('/send', [MobilePaymentController::class, 'makeTransfer']);
    Route::delete('/recipients/{id}', [MobilePaymentController::class, 'deleteRecipient']);

    // Antar Bank
    Route::get('/daftar-bank', fn() => Inertia::render('Mpayment/DaftarBank'));
    Route::get('/transfer-antar-bank', fn() => Inertia::render('Mpayment/TransferAntarBank'));

    Route::get('/bank-recipients', [MobilePaymentController::class, 'getBankRecipients']);
    Route::post('/bank-recipients', [MobilePaymentController::class, 'storeBankRecipient']);
    Route::post('/interbank-transfer', [MobilePaymentController::class, 'makeInterbankTransfer']);
    Route::get('/interbank-history', [MobilePaymentController::class, 'interbankHistory']);

   
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/m-payment/ewallet', function () {
    $recentEwalletTransactions = EWalletTransaction::where('user_id', auth()->id())
        ->latest()
        ->take(5)
        ->get();

    return Inertia::render('Mpayment/Ewallet', [
        'user' => auth()->user(),
        'recentTransactions' => $recentEwalletTransactions,
    ]);
})->middleware('auth');

Route::post('/e-wallet/transfer', [EWalletTransactionController::class, 'store'])->name('ewallet.transfer');

require __DIR__.'/auth.php';