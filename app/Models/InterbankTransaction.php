<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterbankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_recipient_id',
        'amount',
        'admin_fee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankRecipient()
{
    return $this->belongsTo(BankRecipient::class, 'bank_recipient_id');
}
}

