<?php

namespace App\Services\TikTok;

use App\Models\TikTokToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TikTokTokenService
{
    protected string $appId;
    protected string $secret;
    protected string $redirectUri;

    public function __construct()
    {
        $this->appId       = config('services.tiktok.app_id');
        $this->secret      = config('services.tiktok.secret');
        $this->redirectUri = config('services.tiktok.redirect_uri');
    }

    public function getAccessToken(): string
    {
        $token = TikTokToken::latest()->first();

        if (!$token) {
            throw new Exception('No TikTok token record found. Please authenticate first.');
        }

        if ($token->expires_at && now()->lt($token->expires_at)) {
            return $token->access_token;
        }

        if ($token->refresh_token) {
            $newToken = $this->refreshToken($token->refresh_token);
            if ($newToken) {
                return $newToken->access_token;
            }
        }

        throw new Exception('No valid TikTok token found and refresh failed.');
    }

    public function exchangeCode(string $authCode): ?TikTokToken
    {
        $params = [
            'app_id'     => $this->appId,
            'secret'     => $this->secret,
            'auth_code'  => $authCode,
            'grant_type' => 'authorization_code',
        ];

        return $this->requestAndSave('https://business-api.tiktokglobalshop.com/open_api/v1.3/oauth2/access_token/', $params);
    }

    public function refreshToken(string $refreshToken): ?TikTokToken
    {
        $params = [
            'app_id'        => $this->appId,
            'secret'        => $this->secret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        return $this->requestAndSave('https://business-api.tiktokglobalshop.com/open_api/v1.3/oauth2/refresh_token/', $params);
    }

    protected function requestAndSave(string $url, array $params): ?TikTokToken
    {
        try {
            $res = Http::asForm()->post($url, $params);
            $res->throw();

            $data = $res->json('data');

            if (!isset($data['access_token'])) {
                Log::error("TikTok OAuth response missing access_token", ['response' => $data]);
                return null;
            }

            $payload = [
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? optional(TikTokToken::latest()->first())->refresh_token,
                'expires_at'    => isset($data['expires_in']) ? Carbon::now()->addSeconds($data['expires_in']) : null,
            ];

            return TikTokToken::updateOrCreate([], $payload);

        } catch (Exception $e) {
            Log::error("TikTok OAuth error", ['error_message' => $e->getMessage()]);
            return null;
        }
    }
}
