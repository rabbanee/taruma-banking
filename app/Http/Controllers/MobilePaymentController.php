<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobilePayment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\TransferTransaction;
use App\Models\InterbankTransaction;
Use App\Models\BankRecipient;
use Illuminate\Support\Facades\Log;

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
            'token_number' => 'required|string',
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
                'customer_number' => $validated['token_number'],
                'metadata' => [
                    'token_number' => $validated['token_number'],
                    'error_message' => null,
                ]
                
                 
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
   public function air()
    {
        // Data wilayah PDAM untuk dropdown
        $pdamRegions = [
            [
                'id' => 'jabodetabek',
                'name' => 'Jabodetabek',
                'districts' => [
                    ['code' => 'JKT01', 'name' => 'PDAM Jaya - Jakarta Pusat'],
                    ['code' => 'JKT02', 'name' => 'PDAM Jaya - Jakarta Barat'],
                    ['code' => 'JKT03', 'name' => 'PDAM Jaya - Jakarta Selatan'],
                    ['code' => 'JKT04', 'name' => 'PDAM Jaya - Jakarta Timur'],
                    ['code' => 'JKT05', 'name' => 'PDAM Jaya - Jakarta Utara'],
                    ['code' => 'DPK01', 'name' => 'PDAM Kota Depok'],
                    ['code' => 'BGR01', 'name' => 'PDAM Kota Bogor'],
                    ['code' => 'TGR01', 'name' => 'PDAM Kab. Tangerang'],
                ]

            ],
            
           [
                'id' => 'jabar',
            'name' => 'Jawa Barat',
            'districts' => [
                ['code' => 'BDG01', 'name' => 'PDAM Kota Bandung'],
                ['code' => 'CRB01', 'name' => 'PDAM Kota Cirebon'],
                ['code' => 'TSK01', 'name' => 'PDAM Kota Tasikmalaya'],
                ['code' => 'BJR01', 'name' => 'PDAM Kota Banjar'],
            ]
                
            ],

            [
                'id' => 'jateng',
            'name' => 'Jawa Tengah',
            'districts' => [
            ['code'=> 'SMG01', 'name' => 'PDAM Kota Semarang'],
            ['code'=> 'SRG01', 'name' => 'PDAM Kota Surakarta'],
            ['code'=> 'PWT01', 'name' => 'PDAM Kota Purwokerto'],
            ['code'=> 'TGL01', 'name' => 'PDAM Kota Tegal' ],

            ]

            ],
             
            [
            'id' => 'jatim',
            'name' => 'Jawa Timur',
            'districts' => [
                ['code' => 'SBY01', 'name' => 'PDAM Surya Sembada - Surabaya'],
                ['code' => 'MLG01', 'name' => 'PDAM Kota Malang'],
                ['code' => 'JBR01', 'name' => 'PDAM Kota Jember'],
                ['code' => 'MJK01', 'name' => 'PDAM Kota Mojokerto'],
            ]
             
            ],

            [
            'id' => 'bali',
            'name' => 'Bali',
            'districts'=>[
                ['code' => 'DPS01', 'name' => 'PDAM Denpasar'],
                ['code' => 'BAD01', 'name' => 'PDAM Kab. Badung'],
                ['code' => 'GIY01', 'name' => 'PDAM Kab. Gianyar'],
            ]

            ],

            [
                'id' => 'NTB',
                'name' => 'Nusa Tenggara Barat',
                'districts' => [
                    ['code' => 'NTB01', 'name' => 'PDAM Kota Mataram'],
                    ['code' => 'NTB02', 'name' => 'PDAM Kota Sumbawa'],
                    ['code' => 'NTB03', 'name' => 'PDAM Kota Lombok'],
                    ['code' => 'NTB04', 'name' => 'PDAM Kota Bima'],
                ]
                ],

            [
                'id' =>'DIY',
                'name' => 'DI Yogyakarta',
                'districts' => [
                    ['code' => 'YOG01', 'name' => 'PDAM Kota Yogyakarta'],
                    ['code' => 'YOG02', 'name' => 'PDAM Kota Sleman'],
                    ['code' => 'YOG03', 'name' => 'PDAM Kota Bantul'],
                    ['code' => 'YOG04', 'name' => 'PDAM Kota Gunungkidul'],
                ]
            ]

        ];

        // Ambil transaksi terakhir untuk user
        $recentTransactions = MobilePayment::where('user_id', Auth::id())
            ->where('payment_type', 'pdam')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return inertia('Mpayment/Air', [
            'user' => Auth::user(),
            'pdamRegions' => $pdamRegions,
            'recentTransactions' => $recentTransactions
        ]);
    }

    public function purchaseAir(Request $request)
    {
        // Validasi sesuai field yang dikirim dari Vue
        $validated = $request->validate([
             'customer_number' => 'required|string',
            'region' => 'required|string',
    'district_code' => 'required|string',
    'amount' => 'required|integer|min:20000',
        ]);

          DB::beginTransaction();
      try{
        // Dapatkan nama wilayah dan cabang
        $districtName = $this->getDistrictName($validated['district_code']);
        $regionName = $this->getRegionName($validated['region']);

        $payment = MobilePayment::create([
             'user_id' => Auth::id(),
             'payment_type' => 'pdam',
            'amount' => $validated['amount'],
            'status' => 'success',
           'transaction_id' => 'PDAM-' . now()->format('YmdHis'),
            'customer_number' => $validated['customer_number'],
            'paid_at' => now(),
            'metadata' => [
        'region_id' => $validated['region'],
        'region_name' => $regionName,
        'district_code' => $validated['district_code'],
        'district_name' => $districtName,
        ]
        ]);

          DB::commit();
        return response()->json([
            'success' => true,
            'data' => [
                'transaction_id' => $payment->transaction_id,
                'customer_number' => $payment->customer_number,
                'amount' => $payment->amount,
                'region' => $regionName,
                'district_name' => $districtName,
                'date' => $payment->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),

            ]

            
        ]);
    } catch (\Exception $e) {
    DB::rollBack(); 
    return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan transaksi.',
        'error' => $e->getMessage(), // TAMPILKAN DETAIL ERROR
    ], 500);
}
      
    }

    private function getDistrictName($code)
    {
        $districts = [
            'JKT01' => 'PDAM Jaya - Jakarta Pusat',
            'JKT02' => 'PDAM Jaya - Jakarta Barat',
            'JKT03' => 'PDAM Jaya - Jakarta Selatan',
            'JKT04' => 'PDAM Jaya - Jakarta Timur',
            'JKT05'=> 'PDAM Jaya - Jakarta Utara' ,
            'DPK01' => 'PDAM Kota Depok',
            'BGR01' => 'PDAM Kota Bogor',
            'TGR01' => 'PDAM Kab. Tangerang',
            'BDG01' =>  'PDAM Kota Bandung',
            'CRB01' => 'PDAM Kota Cirebon' ,
            'TSK01' => 'PDAM Kota Tasikmalaya',
            'BJR01' => 'PDAM Kota Banjar',
            'MLG01' => 'PDAM Kota Malang',
            'JBR01' => 'PDAM Kota Jember',
            'MJK01' => 'PDAM Kota Mojokerto',
            'DPS01' => 'PDAM Denpasar',
            'BAD01' => 'PDAM Kab. Badung',
            'GIY01' => 'PDAM Kab. Gianyar',
            'NTB01' => 'PDAM Kota Mataram',
            'NTB02' => 'PDAM Kota Sumbawa',
            'NTB03' => 'PDAM Kota Lombok',
            'NTB04' => 'PDAM Kota Bima',
            'YOG01' => 'PDAM Kota Yogyakarta',
            'YOG02' => 'PDAM Kab. Sleman',
            'YOG03' => 'PDAM Kab. Bantul',
            'YOG04' => 'PDAM Kab. Gunung Kidul',


        ];

        return $districts[$code] ?? 'Cabang PDAM';
    }

    private function getRegionName($id)
    {
        $regions = [
            'jabodetabek' => 'Jabodetabek',
            'jabar' => 'Jawa Barat',
            'jateng' => 'Jawa Tengah',
            'jatim' => 'Jawa Timur',
            'bali' => 'Bali',
        ];

        return $regions[$id] ?? 'Wilayah PDAM';
    }
    //  Halaman Transfer (Pascabayar)
 public function menu()
    {
        return Inertia::render('Mpayment/Menu');
    }

    public function daftarRekening()
    {
        return Inertia::render('Mpayment/DaftarRekening');
    }

    public function transfer()
    {
        return Inertia::render('Mpayment/Transfer');
    }

    public function getRecipients()
{
    return response()->json(Auth::user()->transferRecipients);
}

public function storeRecipient(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'account_number' => 'required|digits_between:1,10|regex:/^\d+$/|unique:transfer_recipients,account_number',
    ]);

    $recipient = Auth::user()->transferRecipients()->create([
        'name' => $request->name,
        'account_number' => $request->account_number,
    ]);

    return response()->json($recipient);
}

public function makeTransfer(Request $request)
{
    $request->validate([
        'recipient_id' => 'required|exists:transfer_recipients,id',
        'amount' => 'required|numeric|min:1000',
    ]);

    $transfer = TransferTransaction::create([
        'user_id' => auth()->id(),
        'recipient_id' => $request->recipient_id,
        'amount' => $request->amount,
    ]);

    return response()->json($transfer);
}

public function transactionHistory()
{
    $history = TransferTransaction::with('recipient')
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return response()->json($history);
}

public function deleteRecipient($id)
{
    $recipient = auth()->user()->transferRecipients()->findOrFail($id);
    $recipient->delete();

    return response()->json(['message' => 'Berhasil dihapus']);
}

public function storeBankRecipient(Request $request)
{
    $request->validate([
        'bank_name' => 'required|string',
        'account_name' => 'required|string',
        'account_number' => 'required|string|max:20',
    ]);

    return Auth::user()->bankRecipients()->create($request->all());
}

public function interbankHistory()
{
    $history = InterbankTransaction::with('bankRecipient')
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return response()->json($history);
}


public function getBankRecipients()
{
    return response()->json(Auth::user()->bankRecipients);
}

public function makeInterbankTransfer(Request $request)
{
    try {
        $request->validate([
            'bank_recipient_id' => 'required|exists:bank_recipients,id',
            'amount' => 'required|numeric|min:10000',
        ]);

        $recipient = BankRecipient::findOrFail($request->bank_recipient_id);

        $bank_name = trim($recipient->bank_name);
        $fee = strcasecmp($bank_name, 'Taruma Bank') === 0
           ? 0
            : round($request->amount * 0.03);


        $transfer = InterbankTransaction::create([
            'user_id' => auth()->id(),
            'bank_recipient_id' => $recipient->id,
            'amount' => $request->amount,
            'admin_fee' => $fee,
        ]);

        return response()->json(['transfer' => $transfer, 'fee' => $fee]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
        ], 500);
    }
}


}
