<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscripe extends Model
{
    //
        protected $fillable = [
        'user_id',
        'plan',
        'price',
        'start_date',
        'end_date',
    ];

      public function user()
    {
        return $this->belongsTo(User::class);
    }
}
