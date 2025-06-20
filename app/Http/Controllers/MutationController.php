<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\MobilePayment;
use App\Models\EWalletTransaction;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use App\Models\InterbankTransaction;
use App\Models\TransferTransaction;

class MutationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $mobile = MobilePayment::where('user_id', $userId)
            ->select('payment_type', 'amount', 'status', 'created_at')
            ->get();

        $ewallet = EWalletTransaction::where('user_id', $userId)
            ->selectRaw("CONCAT('E-Wallet: ', wallet_type) as payment_type, amount, status, created_at")
            ->get();

        $interbank = InterbankTransaction::where('user_id', $userId)
            ->selectRaw("CONCAT('Transfer antar Bank ', '') as payment_type, amount, status, created_at")
            ->get();

        $transfer = TransferTransaction::where('user_id', $userId)
            ->selectRaw("CONCAT('Transfer antar Rekening ', '') as payment_type, amount, status, created_at")
            ->get();

        $merged = $mobile->concat($ewallet)
                         ->concat($interbank)
                         ->concat($transfer)
                         ->sortByDesc('created_at')
                         ->values();

        return Inertia::render('Mutation', [
            'mutation' => $merged
        ]);
    }
}
