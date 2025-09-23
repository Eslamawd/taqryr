<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\TikTokToken;
use Carbon\Carbon;

class TikTokTokenCommand extends Command
{
    protected $signature = 'tiktok:token {--code=} {--refresh=}';
    protected $description = 'Get or refresh TikTok Ads OAuth token';

    public function handle()
    {
        $appId       = config('services.tiktok.app_id');
        $secret      = config('services.tiktok.secret');
        $redirectUri = config('services.tiktok.redirect_uri');

        if ($this->option('refresh')) {
            $rec = TikTokToken::latest()->first();
            if (!$rec || !$rec->refresh_token) {
                return $this->error("No token to refresh.");
            }
            $url = "https://business-api.tiktokglobalshop.com/open_api/v1.3/oauth2/refresh_token/";
            $params = [
                'app_id'        => $appId,
                'secret'        => $secret,
                'grant_type'    => 'refresh_token',
                'refresh_token' => $rec->refresh_token,
            ];
        } else {
            if (!$this->option('code')) {
                return $this->error("You must pass --code=CODE from OAuth redirect.");
            }
            $url = "https://business-api.tiktokglobalshop.com/open_api/v1.3/oauth2/access_token/";
            $params = [
                'app_id'       => $appId,
                'secret'       => $secret,
                'grant_type'   => 'authorization_code',
                'auth_code'    => $this->option('code'),
                'redirect_uri' => $redirectUri,
            ];
        }

        $res = Http::asForm()->post($url, $params);

        if ($res->failed()) {
            return $this->error("Error: " . $res->body());
        }

        $data = $res->json('data'); // TikTok بيرجع الـ tokens تحت "data"

        if (!isset($data['access_token'])) {
            return $this->error("No access_token found in TikTok response: " . json_encode($res->json()));
        }

        TikTokToken::updateOrCreate([], [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at'    => isset($data['expires_in']) ? Carbon::now()->addSeconds($data['expires_in']) : null,
        ]);

        $this->info("TikTok token saved successfully.");
    }
}
