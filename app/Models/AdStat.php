<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdStat extends Model
{
    //
       protected $fillable = ['ad_id', 'stat_date','granularity','impressions','swipes','spend','raw'];

    protected $casts = [
        'raw' => 'array',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
