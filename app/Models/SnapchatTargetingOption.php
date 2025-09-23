<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnapchatTargetingOption extends Model
{
    protected $fillable = ['type', 'country_code', 'options'];
    protected $casts = ['options' => 'array'];

  public static function getFreshTargetingOptions(string $countryCode)
    {
        $countryCode = strtolower($countryCode);

        $lastUpdate = self::where('country_code', $countryCode)
            ->latest('updated_at')
            ->value('updated_at');

        if (!$lastUpdate) {
            app(\App\Services\Snapchat\SnapchatTargetingSyncService::class)->syncAll($countryCode);
        } elseif ($lastUpdate < now()->subDay()) {
            app(\App\Services\Snapchat\SnapchatTargetingSyncService::class)->syncAll($countryCode);
        }

        return self::where('country_code', $countryCode)->get();
    }

}