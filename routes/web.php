<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MyBalanceController;
use App\Http\Controllers\MutationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MobilePaymentController;
use App\Http\Controllers\PaymentController;
use App\Models\EWalletTransaction;
use App\Http\Controllers\EWalletTransactionController;
use App\Http\Controllers\BeneficiaryController;

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
        // Route::post('/m-payment/air/purchase', [MobilepaymentController::class, 'purchaseAir'])->name('m-payment.air.purchase');

        // Route E-Wallet
        Route::get('/ewallet', function () {
            $user = auth()->user();
            $recentEwalletTransactions = EWalletTransaction::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
            // return Inertia::render('Mpayment/Ewallet');
            return Inertia::render('Mpayment/Ewallet', [
                'user' => $user,
                'recentTransactions' => $recentEwalletTransactions,
            ]);
        })->name('ewallet');
        // Route Transfer
        Route::get('/transfer', [MobilePaymentController::class, 'transfer'])->name('transfer');
        Route::post('/transfer/beneficiary', [MobilePaymentController::class, 'addBeneficiary'])->name('transfer.beneficiary');
        Route::post('/transfer/store', [MobilePaymentController::class, 'storeTransfer'])->name('transfer.store');

    });

});

Route::middleware(['auth'])->group(function () {
    Route::get('/my-balance', [MyBalanceController::class, 'show'])->name('my-balance');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-balance/mutation', [MutationController::class, 'index'])->name('mutation.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('/m-payment/ewallet', function () {
//     $recentEwalletTransactions = EWalletTransaction::where('user_id', auth()->id())
//         ->latest()
//         ->take(5)
//         ->get();

//     return Inertia::render('Mpayment/Ewallet', [
//         'user' => auth()->user(),
//         'recentTransactions' => $recentEwalletTransactions,
//     ]);
// })->middleware('auth');

Route::post('/e-wallet/transfer', [EWalletTransactionController::class, 'store'])->name('ewallet.transfer');

require __DIR__.'/auth.php';
