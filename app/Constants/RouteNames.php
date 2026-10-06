<?php

namespace App\Constants;

final class RouteNames
{
    public const LOGIN = 'login';
    public const LOGIN_STORE = 'login.store';
    public const LOGOUT = 'logout';
    public const DASHBOARD = 'dashboard';
    public const PROFILE = 'perfil.edit';
    public const EMPRESA = 'empresa.edit';
    public const USUARIOS = 'usuarios.index';
    public const ADMIN_EMPRESAS = 'admin.empresas.index';
    public const ROLES = 'roles.index';
    public const PERMISOS = 'permisos.index';

    private function __construct() {}
}
