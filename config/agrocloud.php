<?php

return [
    'bootstrap_admin' => [
        'email' => env('AGROCLOUD_ADMIN_EMAIL'),
        'password' => env('AGROCLOUD_ADMIN_PASSWORD'),
        'name' => env('AGROCLOUD_ADMIN_NAME', 'Administrador'),
        'lastname' => env('AGROCLOUD_ADMIN_LASTNAME', 'AgroCloud'),
    ],
];
