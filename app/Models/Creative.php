<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creative extends Model
{
    protected $fillable = [
        'user_id',
        'file_path',
        'ad_id',
        'media_id',
        'platform',
        'type',
        'platform_creative_id',
    ];
    protected $appends = ['file_url'];

   public function getFileUrlAttribute()
{
    if (!$this->file_path) return null;

    if (str_starts_with($this->file_path, 'http')) {
        return $this->file_path;
    }

    return asset('storage/' . $this->file_path);
}


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ads()
    {
        return $this->belongsTo(Ad::class);
    }
}
