<?php

use App\Constants\RouteNames;

return [
    'sidebar' => [
        [
            'label' => 'Dashboard',
            'route' => RouteNames::DASHBOARD,
            'active' => 'dashboard',
            'icon' => 'dashboard',
            'permission' => null,
        ],
        [
            'label' => 'Empresa',
            'route' => RouteNames::EMPRESA,
            'active' => 'empresa.*',
            'icon' => 'building',
            'permission' => 'empresa.ver',
        ],
        [
            'label' => 'Usuarios',
            'route' => RouteNames::USUARIOS,
            'active' => 'usuarios.*',
            'icon' => 'users',
            'permission' => 'usuarios.ver',
        ],
        [
            'label' => 'Roles',
            'route' => RouteNames::ROLES,
            'active' => 'roles.*',
            'icon' => 'shield',
            'permission' => 'roles.ver',
        ],
        [
            'label' => 'Permisos',
            'route' => RouteNames::PERMISOS,
            'active' => 'permisos.*',
            'icon' => 'key',
            'permission' => 'permisos.ver',
        ],
    ],
];
