<?php

namespace App\Services\Snapchat;

use App\Models\SnapchatToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;

class SnapchatTokenService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;

    public function __construct()
    {
        $this->clientId     = config('services.snapchat.client_id');
        $this->clientSecret = config('services.snapchat.client_secret');
        $this->redirectUri  = config('services.snapchat.redirect_uri');
    }

    /**
     * Get a valid access token (refresh if needed).
     *
     * @return string
     * @throws Exception
     */
    public function getAccessToken(): string
    {
        // Force a fresh database connection to avoid "stale connection" issues in a queue worker.
        DB::reconnect();
        
        $token = SnapchatToken::latest()->first();

        if (!$token) {
            throw new Exception('No Snapchat token record found. Please authenticate via OAuth first.');
        }

        // Check if the token is still valid.
        if ($token->expires_at && now()->lt($token->expires_at)) {
            return $token->access_token;
        }

        // If the token has expired, try to refresh it.
        if ($token->refresh_token) {
            $newToken = $this->refreshToken($token->refresh_token);
            if ($newToken) {
                return $newToken->access_token;
            }
        }

        throw new Exception('No valid Snapchat access token found and refresh failed.');
    }

    /**
     * Exchange authorization code for token (first time use).
     *
     * @param string $code
     * @return SnapchatToken|null
     */
    public function exchangeCode(string $code): ?SnapchatToken
    {
        $params = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri,
        ];

        return $this->requestAndSave($params);
    }

    /**
     * Refresh token explicitly (manual or auto).
     *
     * @param string $refreshToken
     * @return SnapchatToken|null
     */
    public function refreshToken(string $refreshToken): ?SnapchatToken
    {
        $params = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ];

        return $this->requestAndSave($params);
    }

    /**
     * Private helper: make HTTP call & save token (update or insert).
     *
     * @param array $params
     * @return SnapchatToken|null
     */
    protected function requestAndSave(array $params): ?SnapchatToken
    {
        try {
            // Request to Snapchat OAuth v2 endpoint
            $res = Http::asForm()->post('https://accounts.snapchat.com/login/oauth2/access_token', $params);

            // Throws an exception for client or server errors (4xx, 5xx).
            $res->throw();

            $data = $res->json();

            // Check if access_token is present in the response.
            if (!isset($data['access_token'])) {
                Log::error("Snapchat OAuth response missing access_token", ['response' => $data]);
                return null;
            }

            $payload = [
                'access_token'  => $data['access_token'],
                // Use the new refresh_token if provided, otherwise keep the old one.
                'refresh_token' => $data['refresh_token'] ?? optional(SnapchatToken::latest()->first())->refresh_token,
                'expires_at'    => isset($data['expires_in']) ? Carbon::now()->addSeconds($data['expires_in']) : null,
            ];

            // Use updateOrCreate to ensure only one record is present.
            // It will update the existing record or create a new one if it doesn't exist.
            $token = SnapchatToken::updateOrCreate(
                [], // This empty array ensures we always target the first (or only) row.
                $payload
            );

            return $token;
        } catch (RequestException $e) {
            // Log specific HTTP errors (like 404 or 401).
            Log::error("Snapchat OAuth request failed with status " . $e->response->status(), [
                'error_message' => $e->getMessage(),
                'response_body' => $e->response->body()
            ]);
            return null;
        } catch (Exception $e) {
            // Log any other unexpected errors.
            Log::error("Snapchat OAuth an unexpected error occurred", [
                'error_message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
