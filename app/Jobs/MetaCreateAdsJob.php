<?php

namespace App\Jobs;

use App\Models\Ad;

use App\Services\Meta\CreateFacebookAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MetaCreateAdsJob implements ShouldQueue
{
    
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */

    protected Ad $ad;
    public function __construct(Ad $ad)
    {
        //
        $this->ad = $ad;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //

    app(CreateFacebookAdsService::class, ['ad' => $this->ad])->newFacebookAd();

    }
}
