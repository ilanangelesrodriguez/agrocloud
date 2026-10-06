<?php

use App\Constants\RouteNames;

return [
    'sidebar' => [
        [
            'section' => 'PRINCIPAL',
            'label' => 'Dashboard',
            'route' => RouteNames::DASHBOARD,
            'active' => 'dashboard',
            'icon' => 'dashboard',
            'permission' => 'dashboard.ver',
        ],
        [
            'section' => 'ADMINISTRACIÓN',
            'label' => 'Mi empresa',
            'route' => RouteNames::EMPRESA,
            'active' => 'empresa.*',
            'icon' => 'building',
            'permission' => 'empresa.ver',
            'requires_company' => true,
        ],
        [
            'section' => 'ADMINISTRACIÓN',
            'label' => 'Usuarios',
            'route' => RouteNames::USUARIOS,
            'active' => 'usuarios.*',
            'icon' => 'users',
            'permission' => 'usuarios.ver',
            'requires_company' => true,
        ],
        [
            'section' => 'SISTEMA',
            'label' => 'Empresas',
            'route' => RouteNames::ADMIN_EMPRESAS,
            'active' => 'admin.empresas.*',
            'icon' => 'building-plus',
            'permission' => 'empresas.ver',
            'superadmin' => true,
        ],
        [
            'section' => 'SISTEMA',
            'label' => 'Roles',
            'route' => RouteNames::ROLES,
            'active' => 'roles.*',
            'icon' => 'shield',
            'permission' => 'roles.ver',
            'superadmin' => true,
        ],
        [
            'section' => 'SISTEMA',
            'label' => 'Permisos',
            'route' => RouteNames::PERMISOS,
            'active' => 'permisos.*',
            'icon' => 'key',
            'permission' => 'permisos.ver',
            'superadmin' => true,
        ],
    ],
];
