@extends('errors.layout')
@section('code', '422')
@section('title', 'No se puede completar esta acción')
@section('description')
El registro no cumple las condiciones necesarias para realizar esta operación.
@endsection
@section('help')
Vuelve al listado correspondiente y revisa el estado del registro. Si estabas completando un formulario, comprueba los datos y los pasos anteriores antes de volver a enviarlo.
@endsection
