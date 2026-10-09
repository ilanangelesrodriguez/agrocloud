<?php

use App\Constants\RouteNames;

return [
    'sidebar' => [

        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL
        |--------------------------------------------------------------------------
        */

        [
            'section' => 'PRINCIPAL',
            'label' => 'Dashboard',
            'route' => RouteNames::DASHBOARD,
            'active' => 'dashboard',
            'icon' => 'dashboard',
            'permission' => 'dashboard.ver',
        ],

        /*
        |--------------------------------------------------------------------------
        | ADMINISTRACIÓN
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | OPERACIONES
        |--------------------------------------------------------------------------
        */

        [
            'section' => 'OPERACIONES',
            'label' => 'Productores',
            'route' => 'productores.index',
            'active' => 'productores.*',
            'icon' => 'users',
            'permission' => 'productores.ver',
            'requires_company' => true,
        ],
[
    'section' => 'OPERACIONES',
    'label' => 'Lotes',
    'route' => 'lotes.index',
    'active' => 'lotes.*',
    'icon' => 'briefcase',
    'permission' => 'lotes.ver',
    'requires_company' => true,
],
        [
            'section' => 'OPERACIONES',
            'label' => 'Muestreos',
            'route' => 'muestreos.index',
            'active' => 'muestreos.*',
            'icon' => 'clipboard-list',
            'permission' => 'muestreos.ver',
            'requires_company' => true,
        ],
        [
            'section' => 'OPERACIONES',
            'label' => 'Inspecciones',
            'route' => 'inspecciones.index',
            'active' => 'inspecciones.*',
            'icon' => 'search',
            'permission' => 'inspecciones.ver',
            'requires_company' => true,
        ],
        [
            'section' => 'CONTROL DE CALIDAD',
            'label' => 'Dictámenes pendientes',
            'route' => 'calidad.index',
            'active' => 'calidad.*',
            'icon' => 'shield',
            'permission' => 'calidad.ver',
            'requires_company' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | SISTEMA
        |--------------------------------------------------------------------------
        */

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
