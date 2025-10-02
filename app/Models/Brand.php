<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    //
        protected $fillable = [
        'name_brand',
        'headline',
    ];

     public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

}
