<?php

namespace App\Services\CreateSnapAds;

use App\Services\Snapchat\SnapchatTokenService;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CampaignsService
{
    protected string $base = 'https://adsapi.snapchat.com';

    protected SnapchatTokenService $tokenService;

       public function __construct(SnapchatTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

        protected function client()
    {
        $token = $this->tokenService->getAccessToken();
        
        // Ensure the token is a valid string before using it.
        if (empty($token) || !is_string($token)) {
            Log::error('Invalid or missing access token. Cannot make Snapchat API request.');
            throw new \RuntimeException('Invalid or missing access token.');
        }

        return Http::withToken($token)->acceptJson();
    }

   public function createCampaign(string $adAccountId, array $data)
{
    try {
        $response = $this->client()->post("{$this->base}/v1/adaccounts/{$adAccountId}/campaigns", [
            'campaigns' => [$data]
        ]);

        if ($response->failed()) {
            Log::error('Failed to create campaign', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    } catch (Throwable $e) {
        Log::error('Error creating Snapchat campaign', [
            'message' => $e->getMessage(),
        ]);
        return null;
    }
}

}