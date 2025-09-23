<?php

namespace App\Services\Meta;

use App\Models\Ad;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateFacebookAdsService
{
    protected CampaignsService $campaignsService;
    protected AdSetsService $adSetsService;
    protected CreativesService $creativesService;
    protected AdsService $adsService;
    protected MediaService $mediaService;
    protected string $pageId;
    protected Ad $ad;

    public function __construct(
        Ad $ad,
        CampaignsService $campaignsService,
        AdSetsService $adSetsService,
        CreativesService $creativesService,
        AdsService $adsService,
        MediaService $mediaService
    ) {
        $this->ad = $ad;
        $this->campaignsService = $campaignsService;
        $this->adSetsService = $adSetsService;
        $this->creativesService = $creativesService;
        $this->adsService = $adsService;
        $this->mediaService = $mediaService;
        $this->pageId = config('services.facebook.page_id');
    }

    public function newFacebookAd()
    {
        try {
            // 1. Campaign
            $campaignRes = $this->campaignsService->createCampaign([
                'name' => $this->ad->name,
                'objective' => 'OUTCOME_TRAFFIC',
                'status' => 'PAUSED',
                'special_ad_categories' => [], // 👈 لازم
            ]);

            $campaignId = $campaignRes['id'] ?? null;
            Log::info("Campaign ID: {$campaignId}");
            if (!$campaignId) return;

            // 2. AdSet
            $adSetRes = $this->adSetsService->createAdSet([
                'name' => $this->ad->name,
                'campaign_id' => $campaignId,
                'lifetime_budget' => $this->ad->budget * 100, 
                'billing_event' => 'IMPRESSIONS',
                'optimization_goal' => 'REACH',
                'bid_strategy' => 'LOWEST_COST_WITHOUT_CAP',
                'start_time' => $this->ad->start_date,
                'end_time' => $this->ad->end_date,
                'targeting' => [
                    'geo_locations' => [
                        'countries' => [$this->ad->target->country],
                    ]
                ],
                'status' => 'PAUSED',
            ]);

            $adSetId = $adSetRes['id'] ?? null;
            Log::info("AdSet ID: {$adSetId}");
            if (!$adSetId) return;

              $creative = $this->ad->creative->first();

              if ($creative->type === 'VIDEO') {
                
                  $videoId = $this->mediaService->uploadVideo($creative->file_path);
                  Log::info("Video ID: {$videoId}");
                  if (!$videoId) return;
              }

              $creative->update([
                'media_id' => $videoId ?? null,
              ]);

              $imageHash = $this->mediaService->uploadImage('images/taqrer.png');

            if ($imageHash) {
                Log::info("✅ Image Hash: " . $imageHash);
            } else {
                Log::error("❌ Failed to upload image");
            }

            // 3. Creative
            $creativeRes = $this->creativesService->createCreative([
                'name' => $this->ad->name,
                'object_story_spec' => [
                    'page_id' => $this->pageId,
                            'video_data' => [ // ✅ هنا التعديل
                                        'message' => $this->ad->objective,
                                        'video_id' => $videoId,
                                        'title'   => $this->ad->name,
                                        'image_hash' => $imageHash ?? null,
                                    ],
             
                ],
            ]);

            $creativeId = $creativeRes['id'] ?? null;
            Log::info("Creative ID: {$creativeId}");
            if (!$creativeId) return;

            // 4. Ad
            $adRes = $this->adsService->createAd([
                'name' => $this->ad->name,
                'adset_id' => $adSetId,
                'creative' => ['creative_id' => $creativeId],
                'status' => 'PAUSED',
            ]);

            $adId = $adRes['id'] ?? null;
            Log::info("Ad ID: {$adId}");

            if ($adId) {
                $this->ad->update([
                    'platform_ad_id' => $adId,
                ]);
            }

        } catch (Throwable $e) {
            Log::error("Facebook Ad creation failed", ['error' => $e->getMessage()]);
        }
    }
}
