<?php

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampaignsService
{
    protected string $accessToken;
    protected string $adAccountId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('services.facebook.access_token');
        $this->adAccountId = config('services.facebook.ad_account_id');
        $this->baseUrl = config('services.facebook.base_url');
    }

    public function createCampaign(array $data): array
    {
        $response = Http::post("{$this->baseUrl}/act_{$this->adAccountId}/campaigns", array_merge($data, [
            'access_token' => $this->accessToken,
        ]))->json();


        Log::info("Create Campaign Raw Response" . json_encode($response));
        return $response;
    }
}
