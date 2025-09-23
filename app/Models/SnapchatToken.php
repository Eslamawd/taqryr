<?php
// app/Models/SnapchatToken.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SnapchatToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $dates = ['expires_at'];

  public static function latestValid()
    {
        return self::where('expires_at', '>', now())->latest()->first();
    }
}
