<?php

// app/Console/Commands/SnapchatTokenCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\SnapchatToken;
use Carbon\Carbon;

class SnapchatTokenCommand extends Command
{
    protected $signature = 'snapchat:token {--code=} {--refresh=}';
    protected $description = 'Get or refresh Snapchat Ads OAuth token';

    public function handle()
    {
        $clientId = config('services.snapchat.client_id');
        $clientSecret = config('services.snapchat.client_secret');
        $redirectUri = config('services.snapchat.redirect_uri');

        if ($this->option('refresh')) {
            $rec = SnapchatToken::latest()->first();
            if (!$rec || !$rec->refresh_token) {
                return $this->error("No token to refresh.");
            }
            $grantType = 'refresh_token';
            $params = [
                'grant_type' => $grantType,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $rec->refresh_token,
            ];
        } else {
            if (!$this->option('code')) {
                return $this->error("You must pass --code=CODE from OAuth redirect.");
            }
            $grantType = 'authorization_code';
            $params = [
                'grant_type' => $grantType,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $this->option('code'),
                'redirect_uri' => $redirectUri,
            ];
        }

        $res = Http::asForm()->post('https://accounts.snapchat.com/login/oauth2/access_token', $params);
        if ($res->failed()) {
            return $this->error("Error: " . $res->body());
        }

        $data = $res->json();
        SnapchatToken::create([
            'access_token' => $data['access_token'],
            'refresh_token'=> $data['refresh_token'] ?? null,
            'expires_at'   => isset($data['expires_in']) ? Carbon::now()->addSeconds($data['expires_in']) : null,
        ]);
        $this->info("Snapchat token saved successfully.");
    }
}
