<?php

namespace App\Jobs;

use App\Models\Country;
use App\Services\Snapchat\SnapchatTargetingSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SnapchatSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function handle(): void
    {
        try {
            $syncService = app(SnapchatTargetingSyncService::class);

            // نجيب كل أكواد الدول من جدول countries
            $countries = Country::pluck('code')->map(fn($c) => strtolower($c));

            foreach ($countries as $countryCode) {
                $syncService->syncAll($countryCode);
            }

            Log::info('Snapchat sync completed successfully for all countries (via Job).');
        } catch (\Throwable $e) {
            Log::error('Snapchat sync job failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
