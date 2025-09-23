<?php
namespace App\Services\CreateSnapAds;

use App\Services\Snapchat\SnapchatTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdSquadsService
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

    public function createAdSquad(string $campaignId, array $data)
    {
        try {
            $response = $this->client()->post("{$this->base}/v1/campaigns/{$campaignId}/adsquads", [
                'adsquads' => [$data]
            ]);

            if ($response->failed()) {
                Log::error('Failed to create adsquad', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (Throwable $e) {
            Log::error('Error creating Snapchat adsquad', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
