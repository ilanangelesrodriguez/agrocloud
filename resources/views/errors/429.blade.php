@extends('errors.layout')
@section('code', '429')
@section('title', 'Demasiados intentos seguidos')
@section('description')
Recibimos varias solicitudes en poco tiempo.
@endsection
@section('help')
Espera unos instantes antes de volver a intentarlo. Si estabas iniciando sesión, revisa el correo y la contraseña antes del siguiente intento.
@endsection
