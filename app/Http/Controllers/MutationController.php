<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\MobilePayment;
use App\Models\EWalletTransaction;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class MutationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $mobile = MobilePayment::where('user_id', $userId)
            ->select('payment_type', 'amount', 'status', 'created_at')
            ->get();

        $ewallet = EWalletTransaction::where('user_id', $userId)
            ->selectRaw("'E-Wallet: ' || wallet_type as payment_type, amount, status, created_at")
            ->get();

        $merged = $mobile->concat($ewallet)
                         ->sortByDesc('created_at')
                         ->values();

        return Inertia::render('Mutation', [
            'mutation' => $merged
        ]);
    }
}
