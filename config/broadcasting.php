<?php

return [
    'default' => env('BROADCAST_CONNECTION', 'log'),

    'connections' => [
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
            ],
            'client_options' => [],
            'client' => [
                'host' => env('REVERB_CLIENT_HOST', env('APP_ENV') === 'production'
                    ? parse_url(env('APP_URL', ''), PHP_URL_HOST)
                    : env('REVERB_HOST', 'localhost')),
                'port' => env('REVERB_CLIENT_PORT', env('APP_ENV') === 'production' ? 443 : env('REVERB_PORT', 8080)),
                'scheme' => env('REVERB_CLIENT_SCHEME', env('APP_ENV') === 'production' ? 'https' : env('REVERB_SCHEME', 'http')),
            ],
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
