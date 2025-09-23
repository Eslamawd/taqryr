<?php

namespace App\Services\Snapchat;

use App\Models\SnapchatTargetingOption;
use App\Services\Snapchat\SnapchatTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

class SnapchatTargetingSyncService
{
    protected string $base = 'https://adsapi.snapchat.com';

    protected SnapchatTokenService $tokenService;

    // The list of officially supported options and their API endpoints.
    protected array $endpoints = [
        'geos:region' => '/v1/targeting/geo/{country}/region',
        'demographics:languages' => '/v1/targeting/demographics/languages',
        'demographics:age_groups' => '/v1/targeting/demographics/age_group',
        'devices:os_type' => '/v1/targeting/device/os_type',
        'interests:slc' => '/v1/targeting/v1/interests/scls',
    ];

    public function __construct(SnapchatTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * A common client method to get a new HTTP client with a valid access token.
     */
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

   
    /**
     * Sync all targeting options from Snapchat API for a specific country.
     */
     public function syncAll(string $countryCode = 'sa'): void
    {
        foreach ($this->endpoints as $type => $endpointTemplate) {
            try {
                // replace {country_code} in the endpoint template
                $endpoint = str_replace('{country}', $countryCode, $endpointTemplate);
                $url = "{$this->base}{$endpoint}";
                $params = $this->getParams($type, $countryCode);

                $response = $this->client()->get($url, $params);

                if ($response->successful()) {
                    $payload = $this->extractData($response->json());

                    SnapchatTargetingOption::updateOrCreate(
                        ['type' => $type, 'country_code' => $countryCode],
                        ['options' => $payload]
                    );

                    Log::info("Snapchat fetch successful for {$type} ({$countryCode})");
                } else {
                    Log::error("Snapchat fetch failed for {$type}", [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
            } catch (Throwable $e) {
                Log::error("Snapchat fetch exception for {$type}: {$e->getMessage()}");
            }
        }

        Log::info("Snapchat targeting taxonomy synced for {$countryCode}.");
    }

    /**
     * Build the query parameters depending on the endpoint type.
     */
    protected function getParams(string $type, string $countryCode): array
    {
           if ($type === 'interests:slc') {
        return ['country_code' => $countryCode];
    }
                return [];
    }

    /**
     * Extract the relevant data from Snapchat API response.
     */


    protected function translateNamesRecursively(array $data, GoogleTranslate $translator): array
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            // لو ده sub-array, نعيد استدعاء نفس الدالة
            $data[$key] = $this->translateNamesRecursively($value, $translator);
        } elseif ($key === 'name' && !empty($value)) {
            // أضف name_ar جنبه
            try {
                $data['name_ar'] = $translator->translate($value);
            } catch (Throwable $e) {
                $data['name_ar'] = $value; // fallback لو الترجمة فشلت
                Log::warning("Translation failed for {$value}: {$e->getMessage()}");
            }
        }
    }
    return $data;
}


protected function extractData(array $json): array
{
    $dimensions = collect($json['targeting_dimensions'] ?? []);
    $translator = new GoogleTranslate('ar');

    return $dimensions
        ->map(function ($item) use ($translator) {
            return $this->translateNamesRecursively($item, $translator);
        })
        ->filter(fn ($x) => !empty($x))
        ->values()
        ->all();
}


}
