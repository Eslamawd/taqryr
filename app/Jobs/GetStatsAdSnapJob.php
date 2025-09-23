<?php

namespace App\Jobs;

use App\Models\Ad;
use App\Models\AdStat;
use App\Services\CreateSnapAds\GetAdService; // ✅ الصح
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetStatsAdSnapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Ad $ad;

    public function __construct(Ad $ad)
    {
        $this->ad = $ad;
    }

    public function handle(): void
    {
        try {
            $service = app(GetAdService::class);
            $data = $service->getAdStats($this->ad->platform_ad_id);

            if (!$data || empty($data['total_stats'][0]['total_stat']['stats'])) {
                Log::warning("No stats returned for ad {$this->ad->id}");
                return;
            }

            $totalStat = $data['total_stats'][0]['total_stat'];
            $stats = $totalStat['stats'] ?? [];

            AdStat::updateOrCreate(
                [
                    'ad_id' => $this->ad->id,
                    'stat_date' => now()->toDateString(), // أو استخدم finalized_data_end_time
                    'granularity' => $totalStat['granularity'] ?? 'TOTAL',
                    'impressions' => $stats['impressions'] ?? 0,
                    'swipes'      => $stats['swipes'] ?? ($stats['clicks'] ?? 0),
                    'spend'       => $stats['spend'] ?? 0,
                    'raw'         => $data,
                ]
            );

            Log::info("Stored Snapchat stats for ad {$this->ad->id}");
        } catch (\Throwable $e) {
            Log::error("Error processing Snapchat stats for ad {$this->ad->id}", [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
