<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
    'sid'        => env('TWILIO_SID'),
    'token'      => env('TWILIO_AUTH_TOKEN'),
    'verify_sid' => env('TWILIO_VERIFY_SID'),
    ],

    'moyasar' => [
    'secret_key' => env('MOYASAR_SECRET_KEY'),
    'publishable_key' => env('MOYASAR_PUBLISHABLE_KEY'),
    ],

  'snapchat' => [
        'access_token' => env('SNAPCHAT_ACCESS_TOKEN'),
        'ad_account_id' => env('SNAPCHAT_AD_ACCOUNT_ID'),
        'client_id' => env('SNAPCHAT_CLIENT_ID'),
        'client_secret' => env('SNAPCHAT_CLIENT_SECRET'),
        'redirect_uri' => env('SNAPCHAT_REDIRCT'),
        'profile_id' => env('SNAPCHAT_PROFILE_ID')
    ],

 'facebook' => [
    'app_id' => env('FB_APP_ID'),
    'app_secret' => env('FB_APP_SECRET'),
    'access_token' => env('FB_ACCESS_TOKEN'),
    'page_id' => env('FB_PAGE_ID'),
    'base_url' => env('FB_BASE_URL'),
    'ad_account_id' => env('FB_AD_ACCOUNT_ID'), // بدون "act_" هنا
],

    'tiktok' => [  
    'app_id'      => env('TIKTOK_APP_ID'),
    'secret'      => env('TIKTOK_SECRET'),
    'redirect_uri'=> env('TIKTOK_REDIRECT_URI'),
    ],



];
