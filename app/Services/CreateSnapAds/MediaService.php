<?php

namespace App\Services\CreateSnapAds;

use App\Services\Snapchat\SnapchatTokenService;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MediaService
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

   public function createMedia(string $adAccountId, array $data)
 {
    try {
        $response = $this->client()->post("{$this->base}/v1/adaccounts/{$adAccountId}/media", [
            'media' => [$data]
        ]);

        if ($response->failed()) {
            Log::error('Failed to create Media', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    } catch (Throwable $e) {
        Log::error('Error creating Snapchat Media', [
            'message' => $e->getMessage(),
        ]);
        return null;
    }
}



public function uploadMedia(string $mediaId, string $filePath, string $type = 'VIDEO')
{
    try {
        // تحقق من وجود الملف في storage/app/public
        if (!Storage::disk('public')->exists($filePath)) {
            Log::error('Media file not found', [
                'path' => Storage::disk('public')->path($filePath)
            ]);
            return null;
        }

        $stream = Storage::disk('public')->readStream($filePath);

        $response = $this->client()
            ->timeout(300) // زوّد وقت المهلة
            ->attach('file', $stream, basename($filePath))
            ->post("{$this->base}/v1/media/{$mediaId}/upload", [
                'type' => $type
            ]);

        // لو الرد فشل
        if ($response->failed()) {
            Log::error('Failed to upload media', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'headers'=> $response->headers(),
            ]);
            return null;
        }

        // نجاح
        return $response->json();

    } catch (Throwable $e) {
        Log::error('Error upload Snapchat Media', [
            'message' => $e->getMessage(),
        ]);
        return null;
    }
}


}