<?php

use App\Constants\RouteNames;

return [
    'sidebar' => [
        [
            'label' => 'Dashboard',
            'route' => RouteNames::DASHBOARD,
            'icon' => 'dashboard',
        ],

        // Cuando implementes un módulo, agrégalo aquí.
        // Ejemplo:
        // [
        //     'label' => 'Lotes',
        //     'route' => 'lotes.index',
        //     'icon' => 'box',
        // ],
    ],
];
