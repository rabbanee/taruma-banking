<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobilePayment extends Model
{
        use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_type',
        'amount',
        'status',
        'transaction_id',
        'customer_number',
        'metadata',
        'pln_token_1',
        'kwh_amount',
        'error_message',
        'paid_at',
    ];

    protected $casts = [
    'metadata' => 'array'
];

    public function user()
    {
        return $this->belongsTo(User::class);

    }
}
