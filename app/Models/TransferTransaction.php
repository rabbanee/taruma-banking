<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_recipient_id',
        'user_id',
        'recipient_user_id',
        'amount',
    ];

    public function recipient()
    {
        return $this->belongsTo(TransferRecipient::class, 'transfer_recipient_id');
    }

    public function recipientUser()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
