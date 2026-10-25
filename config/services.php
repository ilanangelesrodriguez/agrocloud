<?php

return [

    'ruc' => [
        'url' => env('RUC_API_URL', 'https://zodipe.com/api/v1/ruc'),
        'key' => env('RUC_API_KEY'),
        'cache_seconds' => (int) env('RUC_CACHE_SECONDS', 86400),
        'ca_bundle' => env('RUC_CA_BUNDLE', env('CLOUDINARY_CA_BUNDLE')),
        'public_daily_limit' => (int) env('RUC_PUBLIC_DAILY_LIMIT', 10),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'default_channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cloudinary' => [
        'url' => env('CLOUDINARY_URL'),
        'ca_bundle' => env('CLOUDINARY_CA_BUNDLE'),
    ],

    'agrocloud_ai' => [
        'api_key' => env('AGROCLOUD_AI_API_KEY'),
        'ca_bundle' => env('AGROCLOUD_AI_CA_BUNDLE', env('CLOUDINARY_CA_BUNDLE')),
    ],
];
