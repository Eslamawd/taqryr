<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdTarget extends Model
{
    //
     protected $fillable = [
        'ad_id',
        'country',
        'gender',
        'age_min',
        'age_max',
        'interests',
        'options'
    ];


    protected $casts = [
    'interests' => 'array',
    'options'   => 'array',
];

    public function ad()
{
    return $this->belongsTo(Ad::class);
}
}
