<?php

return [

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
