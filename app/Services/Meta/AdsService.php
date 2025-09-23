<?php

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdsService
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

    public function createAd(array $data): array
    {
        $response = Http::post("{$this->baseUrl}/act_{$this->adAccountId}/ads", array_merge($data, [
            'access_token' => $this->accessToken,
        ]))->json();
        
            Log::info("Create Creative Raw Response" . json_encode($response));
        return $response;
    }
}
