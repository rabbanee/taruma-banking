<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_id',
        'amount',
    ];

    public function recipient()
    {
        return $this->belongsTo(TransferRecipient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
