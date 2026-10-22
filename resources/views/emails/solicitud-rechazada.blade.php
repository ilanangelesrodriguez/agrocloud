@extends('emails.layout')
@section('title', 'Resultado de tu solicitud | AgroCloud AI')
@section('preview', 'Revisamos la solicitud de incorporación de tu empresa.')
@section('content')
<p style="margin:0 0 12px;color:#64748b;font-size:12px;font-weight:bold;letter-spacing:1px;">REVISIÓN FINALIZADA</p>
<h1 style="margin:0 0 20px;font-size:26px;line-height:1.3;color:#0f172a;">Resultado de tu solicitud de acceso</h1>
<p>Hola, {{ $s->nombre }}:</p><p>Gracias por el interés en AgroCloud AI. Tras revisar la solicitud de <strong>{{ $s->nombre_comercial }}</strong> (RUC {{ $s->ruc }}), no pudimos aprobar su incorporación en esta oportunidad.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;"><tr><td style="padding:20px;"><p style="margin:0 0 8px;font-size:14px;font-weight:bold;">Observaciones de la revisión</p><p style="margin:0;white-space:pre-line;font-size:14px;">{{ $s->motivo }}</p></td></tr></table>
<p style="font-size:14px;">No se ha creado una cuenta de acceso. Puedes revisar las observaciones y enviar una nueva solicitud con la información actualizada.</p>
<p style="margin-top:28px;"><a href="{{ route('acceso.create') }}" style="color:#15803d;font-weight:bold;">Volver al formulario de solicitud</a></p>
@endsection
