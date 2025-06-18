<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobilePayment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MobilePaymentController extends Controller
{

      public function index()
    {
         return Inertia::render('MPayment/Index', [
            'user' => Auth::user(),
            // 'someOtherData' => 'value',
        ]);
      
    }

    // Halaman PLN Pascabayar
    public function pln()
    {
        // Get user's recent PLN transactions
        $recentTransactions = MobilePayment::where('user_id', Auth::id() ?? 1)
            ->where('payment_type', 'pln_pascabayar')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Mpayment/Pln', [
            'user' => Auth::user(),
            'recentTransactions' => $recentTransactions
            
        ]);
    }
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

    // 
     public function purchasePln(Request $request)
    {
        $validated = $request->validate([
            'token_number' => 'required|digits:12',
            'amount' => 'required|integer|min:20000'
        ]);

        try {
            DB::beginTransaction();

            // Generate transaction ID
            $transactionId = 'PLN' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create payment record dengan field tambahan untuk PLN
            $payment = MobilePayment::create([
                'user_id' => Auth::id() ?? 1,
                'payment_type' => 'pln_pascabayar',
                'amount' => $validated['amount'],
                'status' => 'pending',
                'transaction_id' => $transactionId,
                'token_number' => $validated['token_number'],
                
                
            ]);

            // Simulate PLN API call
            $plnResult = $this->callPlnApi($validated['token_number'], $validated['amount']);
            
            if ($plnResult['success']) {
                // Update payment dengan token hasil
                $payment->update([
                    'status' => 'success',
                    'pln_token_1' => $plnResult['token1'],
                    'kwh_amount' => $plnResult['kwh'],
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pembelian berhasil!',
                    'data' => [
                        'transaction_id' => $transactionId,
                        'token1' => $plnResult['token1'],
                        'kwh' => $plnResult['kwh']
                    ]
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $plnResult['message'],
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => $plnResult['message']
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    // GET /api/m-payment/history
    public function history()
    {
        $payments = MobilePayment::where('user_id', Auth::id() ?? 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }
    public function plnHistory()
    {
        $payments = MobilePayment::where('user_id', Auth::id() ?? 1)
            ->where('payment_type', 'pln_pascabayar')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($payments);
    }

    private function generateTokenNumber($length = 20)
{
    $digits = '';
    for ($i = 0; $i < $length; $i++) {
        $digits .= mt_rand(0, 9);
    }
    return $digits;
}

    // Private method untuk simulasi PLN API
    private function callPlnApi($tokenNumber, $amount)
    {
        // Simulate PLN API response (replace with real API integration)
        sleep(2); // Simulate API delay
        
        // Mock successful response (85% success rate)
        if (rand(1, 100) <= 85) {
            return [
                'success' => true,
                'token1' => $this->generateTokenNumber(20),
                'kwh' => number_format($amount / 1500, 2), // Simulate kWh calculation
                'message' => 'Success'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Token meter tidak ditemukan atau sedang dalam gangguan'
            ];
        }
    }

    // Akhir Halaman PLN (Pascabayar)

    // Halaman Air (Pascabayar)
    



}

