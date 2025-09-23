<?php

namespace App\Models;
use App\Services\CreateSnapAds\GetAdService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Ad extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'name',
        'objective',
        'budget',
        'status',
        'start_date',
        'end_date',
        'targeting',
        'creative_id',
        'platform_ad_id',
    ];

    protected $casts = [
        'targeting' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creative()
    {
        return $this->hasMany(Creative::class);
    }
    public function target()
    {
        return $this->hasOne(AdTarget::class);
    }
    public function stats()
    {
        return $this->hasOne(AdStat::class);
    }

      public function getTodayStatsAttribute()
    {
        $today = now()->toDateString();

        // ✅ لو متخزن يرجعها
        $existing = $this->stats()->whereDate('stat_date', $today)->first();
        if ($existing) {
            return $existing;
        }

        try {
            $service = app(GetAdService::class);
            $data = $service->getAdStats($this->platform_ad_id);

            if (!$data || empty($data['total_stats'][0]['total_stat']['stats'])) {
                Log::warning("No stats returned for ad {$this->id}");
                return null;
            }

            $totalStat = $data['total_stats'][0]['total_stat'];
            $stats = $totalStat['stats'] ?? [];

            // يخزن stats جديدة
            return $this->stats()->create([
                'stat_date'   => $today,
                'granularity' => $totalStat['granularity'] ?? 'TOTAL',
                'impressions' => $stats['impressions'] ?? 0,
                'swipes'      => $stats['swipes'] ?? ($stats['clicks'] ?? 0),
                'spend'       => $stats['spend'] ?? 0,
                'raw'         => $data,
            ]);

        } catch (\Throwable $e) {
            Log::error("Error fetching stats for ad {$this->id}", [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
