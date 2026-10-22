@extends('emails.layout')
@section('title', 'Activa tu cuenta empresarial | AgroCloud AI')
@section('preview', 'Tu empresa fue aprobada. Establece tu contraseña para comenzar.')
@section('content')
<p style="margin:0 0 12px;color:#15803d;font-size:12px;font-weight:bold;letter-spacing:1px;">SOLICITUD APROBADA</p>
<h1 style="margin:0 0 20px;font-size:26px;line-height:1.3;color:#0f172a;">Tu empresa ya tiene su lugar en AgroCloud AI</h1>
<p>Hola, {{ $s->nombre }}:</p>
<p>Aprobamos la incorporación de <strong>{{ $s->nombre_comercial }}</strong>. El siguiente paso es establecer tu contraseña para activar la cuenta administradora de tu empresa.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:24px 0;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;"><tr><td style="padding:16px;font-size:14px;"><strong>Empresa:</strong> {{ $s->nombre_comercial }}<br><strong>RUC:</strong> {{ $s->ruc }}<br><strong>Correo de acceso:</strong> {{ $s->correo }}</td></tr></table>
<table role="presentation" cellspacing="0" cellpadding="0" style="margin:28px 0;"><tr><td bgcolor="#15803d" style="border-radius:8px;"><a href="{{ $url }}" style="display:inline-block;padding:14px 24px;color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold;">Activar mi cuenta empresarial</a></td></tr></table>
<p style="font-size:14px;"><strong>Enlace válido por 72 horas y de un solo uso.</strong> Si recibes una nueva invitación, utiliza el enlace más reciente.</p>
<p style="font-size:14px;">Una vez activada tu cuenta, podrás ingresar y gestionar a los usuarios de tu empresa. Mantén este enlace en privado.</p>
<p style="margin-top:28px;color:#64748b;font-size:12px;">Si el botón no funciona, copia y pega esta dirección en tu navegador:</p><p style="word-break:break-all;font-size:12px;"><a href="{{ $url }}" style="color:#15803d;">{{ $url }}</a></p>
@endsection
