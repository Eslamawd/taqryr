<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscripe extends Model
{
    //
    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];
}
