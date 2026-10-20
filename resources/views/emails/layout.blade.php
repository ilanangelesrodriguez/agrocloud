<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title')</title></head>
<body style="margin:0;padding:0;background-color:#f1f5f3;font-family:Arial,Helvetica,sans-serif;color:#1e293b;line-height:1.7;">
<div style="display:none;max-height:0;overflow:hidden;">@yield('preview')</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f1f5f3;"><tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #dce6e0;border-radius:16px;">
<tr><td style="padding:28px 32px;background-color:#14532d;border-radius:16px 16px 0 0;"><p style="margin:0;color:#ffffff;font-size:23px;font-weight:bold;">AgroCloud <span style="color:#bbf7d0;">AI</span></p><p style="margin:4px 0 0;color:#dcfce7;font-size:13px;">Control de calidad y trazabilidad agrícola</p></td></tr>
<tr><td style="padding:32px;">@yield('content')</td></tr>
<tr><td style="padding:22px 32px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;">Este mensaje corresponde a una solicitud de acceso empresarial a AgroCloud AI. Si no reconoces esta solicitud, puedes ignorarlo.<br>© {{ now()->year }} AgroCloud AI</td></tr>
</table></td></tr></table>
</body></html>
