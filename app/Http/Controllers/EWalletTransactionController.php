<?php

namespace App\Http\Controllers;

use App\Models\EWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EWalletTransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wallet_type' => 'required|in:DANA,OVO,GoPay,ShopeePay',
            'wallet_number' => 'required|string',
            'amount' => 'required|numeric|min:1000',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            if ($user->balance->current_balance < $validated['amount']) {
                DB::commit();
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi.'
                ], 400);
            }

            $transaction = EWalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => $request->wallet_type,
                'wallet_number' => $request->wallet_number,
                'amount' => $request->amount,
                'status' => 'success'
            ]);

            $user->balance()->decrement('current_balance', $validated['amount']);
            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil',
                'data' => $transaction,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }


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
