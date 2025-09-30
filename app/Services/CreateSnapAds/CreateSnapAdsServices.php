<?php
namespace App\Services\CreateSnapAds;

use App\Mail\SendPriceAdsMail;
use App\Models\Ad;
use App\Models\User;
use Carbon\Carbon;
use App\Services\CreateSnapAds\SnapTargetingBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CreateSnapAdsServices
{
    protected AdsService  $adsService;
    protected CampaignsService  $campaignsService;
    protected CreativesService  $creativesService;
    protected AdSquadsService  $adSquadsService;
    protected MediaService  $mediaService;
    protected $adAccountId;
    protected $profileId;
    protected Ad  $ad;

public function __construct(Ad $ad, AdsService $adsService, CampaignsService $campaignsService, CreativesService $creativesService, AdSquadsService $adSquadsService, MediaService $mediaService)
{
    $this->ad = $ad;
    $this->adsService = $adsService;
    $this->campaignsService = $campaignsService;
    $this->creativesService = $creativesService;
    $this->adSquadsService = $adSquadsService;
    $this->mediaService = $mediaService;

    $this->adAccountId = config('services.snapchat.ad_account_id');
    $this->profileId = config('services.snapchat.profile_id');
}

    public function newSnapAd () {

        $startDate = Carbon::parse($this->ad->start_date);
        $endDate   = Carbon::parse($this->ad->end_date);

        $diffDays = $startDate->diffInDays($endDate);

        $daily_budget = $this->ad->budget / $diffDays;
        
        $user = $this->ad->user;

     $responseCampaign =   $this->campaignsService->createCampaign($this->adAccountId,[
         
      "name"=> $this->ad->name,
      "objective"=> "WEBSITE_CONVERSIONS",
      "status"=> "ACTIVE",
      "daily_budget_micro"=> $daily_budget * 1000000,
      "start_time"=> $this->ad->start_date ,
      "end_time"=> $this->ad->end_date,
     ]);
     
    $campaignId = $responseCampaign['campaigns'][0]['campaign']['id'] ?? null;

    Log::info("campaignId: {$campaignId}");
$options = array_merge(
    [
        "country"   => $this->ad->target->country,
        "gender"    => $this->ad->target->gender,
        "age_min"   => $this->ad->target->age_min,
        "interests" => $this->ad->target->interests ?? [],
    ],
    is_string($this->ad->target->options) 
        ? json_decode($this->ad->target->options, true) 
        : ($this->ad->target->options ?? [])
);


$builder   = new SnapTargetingBuilder($options);
$targeting = $builder->build();

     $responseAdSquad =   $this->adSquadsService->createAdSquad($campaignId,
    [
      
        "name"=> $this->ad->name,
        "placement_v2"=> [
          "config"=> "AUTOMATIC"
        ],
        "start_time" => Carbon::parse($this->ad->start_date)->format('Y-m-d\TH:i:s\Z'),
        "end_time"   => Carbon::parse($this->ad->end_date)->format('Y-m-d\TH:i:s\Z'),

        "delivery_constraint"=> "LIFETIME_BUDGET",
        "pixel_id"=> null,
        "campaign_id"=> $campaignId,
      "targeting" => $targeting,
        "cap_and_exclusion_config"=> [
          "frequency_cap_config"=> [
            [
              "frequency_cap_count"=> 2,
              "time_interval"=> 7,
              "frequency_cap_interval"=> "DAYS",
              "frequency_cap_type"=> "IMPRESSIONS"
            ]
          ]
        ],
        "type"=> "SNAP_ADS",
        "conversion_window"=> null,
        "status"=> "ACTIVE",
        "auto_bid"=> true,
        "optimization_goal"=> "IMPRESSIONS",
        "reach_goal"=> 100000,
        "impression_goal"=> 500000,
        "lifetime_budget_micro"=> $daily_budget * 1000000,
        "reach_and_frequency_status"=> "PENDING"
     
    ]);

    $adSquadId = $responseAdSquad['adsquads'][0]['adsquad']['id'] ?? null;

      Log::info("adSquadId: {$adSquadId}");
          if (!$adSquadId) {
            Log::error("Ad Squad creation failed", ['response' => $responseAdSquad]);

              return;
          }

           $creative = $this->ad->creative->first();
          // 1. Create Media
          $responseMedia = $this->mediaService->createMedia($this->adAccountId, [
              "name" => $this->ad->name,
              "type" => $creative->type,
              "ad_account_id" => $this->adAccountId,
          ]);

          $mediaId = $responseMedia['media'][0]['media']['id'] ?? null;

           Log::info("mediaId: {$mediaId}");
          if (!$mediaId) {
              Log::error("Failed to create media", ['response' => $responseMedia]);
              return;
          }

          // 2. Upload Media
         
          $uploadResult = $this->mediaService->uploadMedia(
              $mediaId,
              $creative->file_path,
              $creative->type
          );

          if (!$uploadResult ) {
              Log::error("Media upload failed", ['mediaId' => $mediaId, 'result' => $uploadResult]);
              return;
          }

          // 3. Create Creative
          $responseCreative = $this->creativesService->createCreative($this->adAccountId, [
              "name" => $this->ad->name,
              "ad_account_id" => $this->adAccountId,
              "top_snap_media_id" => $mediaId,
              "type" => "SNAP_AD",
              "profile_properties" => ["profile_id" => $this->profileId],
              "brand_name" => $this->ad->name,
              "headline" => $this->ad->objective,
              "shareable" => true,
          ]);

          $creativeId = $responseCreative['creatives'][0]['creative']['id'] ?? null;
          
           Log::info("creativeId: {$creativeId}");

          if (!$creativeId) {
              Log::error("Failed to create creative", ['response' => $responseCreative]);
              return;
          }

          // 4. Create Ad
          $responseAd = $this->adsService->createAd($adSquadId, [
              "ad_squad_id" => $adSquadId,
              "creative_id" => $creativeId,
              "name" => $this->ad->name,
              "type" => "SNAP_AD",
              "status" => "PAUSED",
          ]);

          $adId = $responseAd['ads'][0]['ad']['id'] ?? null;
          Log::info("adId : {$adId}");

          if (!$adId) {
              Log::error("Failed to create ad", ['response' => $responseAd]);
              return;
          }

          // 5. Save adId to DB
          $this->ad->update([
              'platform_ad_id' => $adId
          ]);

           $totalInCents = (int) round($this->ad->budget * 100);
           $user->withdraw($totalInCents);
          
            Mail::to($user->email)->send(new SendPriceAdsMail($this->ad));


}


     
}
