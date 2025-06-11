<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobilePayment;
use Illuminate\Support\Facades\Auth;

class MobilePaymentController extends Controller
{
    // POST /api/m-payment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $payment = MobilePayment::create([
            'user_id' => Auth::id() ?? 1, // sementara pakai user id 1 kalau belum ada login
            'payment_type' => $validated['payment_type'],
            'amount' => $validated['amount'],
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Payment successful',
            'data' => $payment,
        ]);
    }

    // GET /api/m-payment/history
    public function history()
    {
        $payments = MobilePayment::where('user_id', Auth::id() ?? 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }
}
