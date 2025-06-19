<?php

namespace App\Http\Controllers;

use App\Models\EWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EWalletTransactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'wallet_type' => 'required|in:DANA,OVO,GoPay,ShopeePay',
            'wallet_number' => 'required|string',
            'amount' => 'required|numeric|min:1000',
        ]);

        $transaction = EWalletTransaction::create([
            'user_id' => Auth::id(),
            'wallet_type' => $request->wallet_type,
            'wallet_number' => $request->wallet_number,
            'amount' => $request->amount,
            'status' => 'success'
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil',
            'data' => $transaction,
        ]);
    }

    public function history()
    {
        return EWalletTransaction::where('user_id', Auth::id())->latest()->get();
    }

    public function show($id)
    {
        return EWalletTransaction::where('user_id', Auth::id())->findOrFail($id);
    }
}
