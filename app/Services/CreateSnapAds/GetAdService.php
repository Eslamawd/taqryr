<?php
namespace App\Services\CreateSnapAds;

use App\Services\Snapchat\SnapchatTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetAdService
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

        if (empty($token) || !is_string($token)) {
            Log::error('Invalid or missing access token.');
            throw new \RuntimeException('Invalid or missing access token.');
        }

        return Http::withToken($token)->acceptJson();
    }

    public function getAdStats(string $adsId)
    {
        try {
            $response = $this->client()->get("{$this->base}/v1/ads/{$adsId}/stats");

            if ($response->failed()) {
                Log::error('Failed to get ad stats', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (Throwable $e) {
            Log::error('Error getting Snapchat ad stats', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
