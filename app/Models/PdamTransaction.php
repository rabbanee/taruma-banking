<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdamTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_number',
        'customer_name',
        'city',
        'amount',
        'status',
        'reference',
        'paid_at',
    ];
}
