<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReq extends Model
{
    //
    protected $fillable = [
        'user_id',
        'amount',
        'image',
        'status', // pending, approved, rejected
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

      public function getImageAttribute($value)
    {
        if (!$value) return null;

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        return asset('storage/' . $value);
    }
}
