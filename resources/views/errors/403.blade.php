@extends('errors.layout')
@section('code', '403')
@section('title', 'No tienes acceso a esta sección')
@section('description')
@if(request()->is('roles', 'roles/*', 'permisos', 'permisos/*', 'admin', 'admin/*'))
Esta sección está reservada al superadministrador de AgroCloud. Ser administrador de una empresa no da acceso a la administración global.
@else
Tu cuenta no tiene los permisos necesarios para abrir esta página.
@endif
@endsection
@section('help')
Vuelve a tu espacio de trabajo. Si necesitas acceso, solicita al administrador que revise los permisos de tu cuenta.
@endsection
