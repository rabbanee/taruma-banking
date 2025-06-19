<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = ['user_id', 'beneficiary_id', 'amount', 'status', 'transferred_at'];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
