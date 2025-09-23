<?php
namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaService
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

    public function uploadVideo(string $filePath): ?string
    {
        if (!Storage::disk('public')->exists($filePath)) {
            Log::error('Media file not found', [
                'path' => Storage::disk('public')->path($filePath)
            ]);
            return null;
        }

        // فتح stream
        $stream = Storage::disk('public')->readStream($filePath);

        $response = Http::timeout(300) // ⏳ زيادة الوقت لـ 5 دقيقة
            ->attach('source', $stream, basename($filePath))
            ->post("{$this->baseUrl}/act_{$this->adAccountId}/advideos", [
                'access_token' => $this->accessToken,
            ])
            ->json();

        // قفل stream
        if (is_resource($stream)) {
            fclose($stream);
        }

        Log::info("Video Upload Response", $response);

        return $response['id'] ?? null;
    }

     public function uploadImage(string $filePath): ?string
    {
        $absolutePath = public_path($filePath);

        if (!file_exists($absolutePath)) {
            Log::error("Image not found", ['path' => $absolutePath]);
            return null;
        }

        $response = Http::attach('source', file_get_contents($absolutePath), basename($filePath))
            ->post("{$this->baseUrl}/act_{$this->adAccountId}/adimages", [
                'access_token' => $this->accessToken,
            ])
            ->json();

        Log::info("Image Upload Response", $response);

        return $response['images'][basename($filePath)]['hash'] ?? null;
    }
}
